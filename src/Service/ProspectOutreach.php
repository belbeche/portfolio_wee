<?php

namespace App\Service;

use App\Entity\Prospect;
use App\Entity\ProspectNote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

/**
 * Envoi des e-mails de prospection : premiers contacts et relances.
 *
 * Utilise par la commande app:prospects:contacter ET par le back office,
 * pour que les garde-fous soient les memes partout :
 *   - 15 envois maximum par vague
 *   - les adresses non verifiees (@a-completer...) sont toujours ignorees
 *   - chaque envoi est trace dans le journal du prospect
 *   - la cadence de relance (J+4 puis J+10) est posee automatiquement
 */
class ProspectOutreach
{
    public const MAX_PAR_VAGUE = 15;

    private const SUJET_PREMIER = 'Developpement web et mobile en marque blanche - Walid Belbeche';
    private const SUJET_RELANCE = 'Je reviens vers vous - Walid Belbeche, developpement web et mobile';

    private EntityManagerInterface $em;
    private MailerInterface $mailer;
    private Environment $twig;
    private string $projectDir;

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer, Environment $twig, string $projectDir)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->projectDir = $projectDir;
    }

    /** Un e-mail est envoyable s'il ne contient pas de domaine a completer. */
    public function isSendable(Prospect $prospect): bool
    {
        $email = (string) $prospect->getEmail();

        return '' !== $email && false === strpos($email, 'a-completer');
    }

    /**
     * Les prospects "A contacter" avec une adresse verifiee, prioritaires d'abord.
     *
     * @return Prospect[]
     */
    public function firstContactCandidates(): array
    {
        $candidats = $this->em->getRepository(Prospect::class)
            ->findBy(['status' => Prospect::STATUS_TO_CONTACT], ['priority' => 'ASC']);

        return array_values(array_filter($candidats, [$this, 'isSendable']));
    }

    /**
     * Les prospects dont la relance est due (cadence J+4 puis J+10).
     *
     * @return Prospect[]
     */
    public function dueFollowUps(): array
    {
        $candidats = $this->em->getRepository(Prospect::class)
            ->findBy(['status' => [Prospect::STATUS_CONTACTED, Prospect::STATUS_FOLLOWED_UP]]);

        $candidats = array_filter($candidats, static fn (Prospect $p) => $p->isFollowUpDue());

        return array_values(array_filter($candidats, [$this, 'isSendable']));
    }

    /**
     * Envoie l'e-mail a un prospect, trace le journal et pose la cadence.
     * Ne flush pas : l'appelant flush une fois la vague terminee.
     *
     * @return string|null null si succes, sinon le message d'erreur
     */
    public function send(Prospect $prospect, bool $relance = false, ?string $customMessage = null): ?string
    {
        if (!$this->isSendable($prospect)) {
            return sprintf('adresse non verifiee : %s', (string) $prospect->getEmail());
        }

        $gabarit = $relance ? 'back/prospect/relance_prospect.html.twig' : 'back/prospect/welcome_prospect.html.twig';
        $sujet = $relance ? self::SUJET_RELANCE : self::SUJET_PREMIER;

        $email = (new Email())
            ->from('contact@walidbelbeche.fr')
            ->to($prospect->getEmail())
            ->bcc('wbelbeche.s@gmail.com')
            ->subject($sujet)
            ->html($this->twig->render($gabarit, [
                'prospect' => $prospect,
                'customMessage' => $customMessage,
            ]));

        $offre = $this->projectDir.'/public/docs/offre-walid-belbeche.pdf';
        if (!$relance && is_file($offre)) {
            $email->attachFromPath($offre, 'Offre - Walid Belbeche.pdf');
        }

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $prospect->registerContact();

        $note = (new ProspectNote())
            ->setProspect($prospect)
            ->setType('email')
            ->setContent(sprintf('[%s] Sujet : %s%s',
                $relance ? 'Relance' : 'Premier contact',
                $sujet,
                !$relance ? '. Offre PDF jointe.' : ''));
        $this->em->persist($note);

        return null;
    }

    /**
     * Envoie une vague complete avec le plafond, puis flush.
     *
     * @param Prospect[] $candidats
     *
     * @return array{sent: int, errors: array<string, string>}
     */
    public function sendWave(array $candidats, bool $relance = false, int $limit = self::MAX_PAR_VAGUE): array
    {
        // Une vague de 15 envois avec piece jointe peut depasser le delai des
        // proxys (Cloudflare coupe vers 100 s, nginx journalise alors un 499).
        // On continue donc meme si le navigateur decroche, et on enregistre
        // le journal APRES CHAQUE envoi : une interruption ne peut plus
        // laisser d'e-mail parti sans trace, donc jamais de doublon.
        ignore_user_abort(true);
        @set_time_limit(600);
        // Un serveur SMTP qui ne repond pas doit echouer vite : sans ceci il
        // retient le worker PHP jusqu'a 60 s par operation et le site entier
        // finit par saturer (pm.max_children).
        @ini_set('default_socket_timeout', '15');

        $limit = max(1, min(self::MAX_PAR_VAGUE, $limit));
        $candidats = array_slice($candidats, 0, $limit);

        $sent = 0;
        $errors = [];
        foreach ($candidats as $prospect) {
            $erreur = $this->send($prospect, $relance);
            if (null === $erreur) {
                ++$sent;
                $this->em->flush(); // trace immediatement cet envoi
            } else {
                $errors[(string) $prospect->getCompany()] = $erreur;
            }
        }

        return ['sent' => $sent, 'errors' => $errors];
    }
}
