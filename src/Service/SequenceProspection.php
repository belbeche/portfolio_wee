<?php

namespace App\Service;

use App\Entity\Prospect;
use App\Entity\ProspectEnvoi;
use App\Entity\ProspectNote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * La sequence de prospection, en quatre temps.
 *
 * Le principe qui gouverne tout : chaque message apporte quelque chose de
 * neuf. Une relance qui dit « je reviens vers vous » n'apporte rien, elle
 * rappelle seulement au destinataire qu'il n'a pas eu envie de repondre. Une
 * relance qui apporte une preuve, puis un conseil utilisable sans rien
 * acheter, change la nature de l'echange.
 *
 *   1. J+0   Le constat. Une observation vraie sur SON site, pas un discours.
 *   2. J+4   La preuve. Un projet comparable, avec un chiffre.
 *   3. J+10  Le cadeau. Le diagnostic complet, sans rien demander.
 *   4. J+21  La porte de sortie. On clot proprement, on laisse la porte ouverte.
 *
 * Passe l'etape 4, on n'ecrit plus. Insister au-dela ne convertit pas, ca
 * fait classer l'adresse en indesirable et ca abime la reputation d'envoi.
 */
class SequenceProspection
{
    /** Cadence entre deux etapes, en jours. */
    public const CADENCE = [1 => 0, 2 => 4, 3 => 10, 4 => 21];

    public const ETAPES = [
        1 => ['cle' => 'constat', 'nom' => 'Le constat', 'gabarit' => 'back/prospect/sequence/1_constat.html.twig'],
        2 => ['cle' => 'preuve', 'nom' => 'La preuve', 'gabarit' => 'back/prospect/sequence/2_preuve.html.twig'],
        3 => ['cle' => 'audit', 'nom' => "L'audit offert", 'gabarit' => 'back/prospect/sequence/3_audit.html.twig'],
        4 => ['cle' => 'sortie', 'nom' => 'La porte de sortie', 'gabarit' => 'back/prospect/sequence/4_sortie.html.twig'],
    ];

    private EntityManagerInterface $em;
    private MailerInterface $mailer;
    private Environment $twig;
    private UrlGeneratorInterface $urls;
    private DiagnosticSite $diagnostic;

    public function __construct(
        EntityManagerInterface $em,
        MailerInterface $mailer,
        Environment $twig,
        UrlGeneratorInterface $urls,
        DiagnosticSite $diagnostic
    ) {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->urls = $urls;
        $this->diagnostic = $diagnostic;
    }

    /** L'etape que ce prospect doit recevoir maintenant, ou null. */
    public function etapeDue(Prospect $prospect): ?int
    {
        $suivante = $prospect->getEtape() + 1;

        if ($suivante > 4 || in_array($prospect->getStatus(), Prospect::CLOSED_STATUSES, true)) {
            return null;
        }

        if (1 === $suivante) {
            return Prospect::STATUS_TO_CONTACT === $prospect->getStatus() ? 1 : null;
        }

        $dernier = $prospect->getLastContactedAt();
        if (null === $dernier) {
            return $suivante;
        }

        $joursEcoules = (int) $dernier->diff(new \DateTime())->days;
        $attendu = self::CADENCE[$suivante] - self::CADENCE[$suivante - 1];

        return $joursEcoules >= $attendu ? $suivante : null;
    }

    /**
     * Analyse le site du prospect si ce n'est pas deja fait, ou si c'est vieux.
     */
    public function diagnostiquer(Prospect $prospect, bool $forcer = false): array
    {
        $ancien = $prospect->getDiagnostiqueLe();
        $perime = null === $ancien || $ancien < new \DateTime('-30 days');

        if ($forcer || $perime || null === $prospect->getDiagnostic()) {
            $prospect->setDiagnostic($this->diagnostic->analyser($prospect));
            $this->em->flush();
        }

        return $prospect->getDiagnostic() ?? [];
    }

    /**
     * Envoie l'etape demandee.
     *
     * @return string|null null si succes, sinon le message d'erreur
     */
    public function envoyer(Prospect $prospect, int $etape, bool $essai = false): ?string
    {
        if (!isset(self::ETAPES[$etape])) {
            return 'Etape inconnue : '.$etape;
        }

        $email = (string) $prospect->getEmail();
        if ('' === $email || false !== strpos($email, 'a-completer')) {
            return 'adresse non verifiee : '.$email;
        }

        $diagnostic = $this->diagnostiquer($prospect);
        $constat = $prospect->getConstatPrincipal();

        // Le jeton doit exister AVANT le rendu : c'est lui qui donne son
        // adresse au pixel de suivi et aux liens du message.
        $envoi = new ProspectEnvoi();
        $envoi->setProspect($prospect);
        $envoi->setEtape($etape);

        $sujet = $this->sujet($prospect, $etape, $constat);
        $envoi->setSujet($sujet);

        $html = $this->twig->render(self::ETAPES[$etape]['gabarit'], [
            'prospect' => $prospect,
            'constat' => $constat,
            'constats' => $prospect->getConstats(),
            'diagnostic' => $diagnostic,
            'pixel' => $this->urls->generate('suivi_ouverture', ['jeton' => $envoi->getJeton()], UrlGeneratorInterface::ABSOLUTE_URL),
            // Twig ne sait pas appeler une fonction anonyme rangee dans une
            // variable : on prepare donc les liens suivis a l'avance.
            'liens' => $this->liensSuivis($envoi->getJeton()),
            'essai' => $essai,
        ]);

        $message = (new Email())
            ->from('contact@walidbelbeche.fr')
            ->to($email)
            ->bcc('wbelbeche.s@gmail.com')
            ->subject(($essai ? '[ESSAI] ' : '').$sujet)
            ->html($html);

        try {
            $this->mailer->send($message);
        } catch (\Throwable $e) {
            $this->tracer($prospect, ProspectNote::TYPE_EMAIL_ECHEC, sprintf(
                "Etape %d refusee. Sujet : %s\nErreur du serveur : %s",
                $etape, $sujet, mb_substr($e->getMessage(), 0, 900)
            ));

            return $e->getMessage();
        }

        if ($essai) {
            return null; // rien n'est trace, rien ne bouge
        }

        $this->em->persist($envoi);
        $prospect->setEtape($etape);
        $prospect->registerContact();

        // La cadence de la prochaine etape est posee des maintenant.
        if ($etape < 4) {
            $jours = self::CADENCE[$etape + 1] - self::CADENCE[$etape];
            $prospect->setNextFollowUpAt(new \DateTime('+'.$jours.' days'));
        } else {
            $prospect->setNextFollowUpAt(null);
        }

        $this->tracer($prospect, 'email', sprintf(
            '[Etape %d - %s] Sujet : %s',
            $etape, self::ETAPES[$etape]['nom'], $sujet
        ));

        return null;
    }

    /**
     * Les liens du message, passes par la redirection qui compte les clics.
     *
     * @return array<string, string>
     */
    private function liensSuivis(string $jeton): array
    {
        $destinations = [
            'realisations' => 'https://walidbelbeche.fr/realisations',
            'devis' => 'https://walidbelbeche.fr/devis',
            'methode' => 'https://walidbelbeche.fr/ma-methode',
            'site' => 'https://walidbelbeche.fr',
        ];

        $liens = [];
        foreach ($destinations as $cle => $url) {
            $liens[$cle] = $this->urls->generate(
                'suivi_clic',
                ['jeton' => $jeton, 'vers' => $cle],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $liens;
    }

    /** L'objet du message, construit a partir du constat quand il y en a un. */
    public function sujet(Prospect $prospect, int $etape, ?array $constat): string
    {
        $societe = $prospect->getCompany() ?: 'votre site';

        switch ($etape) {
            case 1:
                if (null !== $constat) {
                    return match ($constat['cle']) {
                        'https' => sprintf('%s s\'affiche en « Non securise » sur Chrome', $societe),
                        'mobile' => sprintf('%s : l\'affichage sur telephone', $societe),
                        'vitesse' => sprintf('%s met %s secondes a s\'ouvrir', $societe, number_format(($prospect->getDiagnostic()['ms'] ?? 0) / 1000, 1, ',', ' ')),
                        'abandon' => sprintf('Une question sur %s', $societe),
                        'injoignable' => sprintf('%s ne repond pas', $societe),
                        default => sprintf('Un detail sur %s', $societe),
                    };
                }

                return 'Developpement web et mobile en marque blanche - Walid Belbeche';

            case 2:
                return sprintf('Ce que ca donne concretement, %s', $prospect->getCity() ? 'pres de '.$prospect->getCity() : 'sur un projet comparable');

            case 3:
                return sprintf('Le point complet sur %s, sans contrepartie', $societe);

            case 4:
            default:
                return 'Je clos, mais la porte reste ouverte';
        }
    }

    private function tracer(Prospect $prospect, string $type, string $contenu): void
    {
        try {
            $note = (new ProspectNote())
                ->setProspect($prospect)
                ->setType($type)
                ->setContent($contenu);
            $this->em->persist($note);
            $this->em->flush();
        } catch (\Throwable $e) {
            error_log('[sequence] trace impossible : '.$e->getMessage());
        }
    }
}
