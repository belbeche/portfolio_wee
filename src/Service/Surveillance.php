<?php

namespace App\Service;

use App\Entity\PushSubscription;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Surveillance du site : quelques controles simples, executes regulierement.
 *
 * Le principe qui gouverne tout ce fichier : on n'alerte QUE sur un
 * changement d'etat. Un service qui tombe declenche un e-mail, un service
 * qui reste tombe n'en declenche plus, et son retour a la normale en
 * declenche un dernier. Une alerte qui se repete toutes les dix minutes
 * finit dans un dossier ignore, et ce jour la elle ne sert plus a rien.
 */
class Surveillance
{
    /** Seuil d'alerte sur l'espace disque restant, en Mo. */
    private const DISQUE_MINI_MO = 200;

    /** Une sauvegarde plus vieille que cela n'en est plus une. */
    private const SAUVEGARDE_MAXI_HEURES = 36;

    private Connection $connexion;
    private EntityManagerInterface $em;
    private Sauvegarde $sauvegarde;
    private PterodactylService $pterodactyl;
    private MailerInterface $mailer;
    private WebPush $push;
    private string $projectDir;

    public function __construct(
        Connection $connexion,
        EntityManagerInterface $em,
        Sauvegarde $sauvegarde,
        PterodactylService $pterodactyl,
        MailerInterface $mailer,
        WebPush $push,
        string $projectDir
    ) {
        $this->connexion = $connexion;
        $this->em = $em;
        $this->sauvegarde = $sauvegarde;
        $this->pterodactyl = $pterodactyl;
        $this->mailer = $mailer;
        $this->push = $push;
        $this->projectDir = $projectDir;
    }

    /**
     * Passe tous les controles.
     *
     * @return array<int, array{cle: string, libelle: string, ok: bool, detail: string}>
     */
    public function controler(bool $leger = false): array
    {
        $controles = [
            $this->controlerBase(),
            $this->controlerDisque(),
            $this->controlerSauvegarde(),
        ];

        if (!$leger) {
            $controles[] = $this->controlerHebergement();
        }

        $controles[] = $this->controlerEnvois();

        return $controles;
    }

    /**
     * Passe les controles et previent si, et seulement si, quelque chose a
     * change depuis la derniere fois.
     *
     * @return array{controles: array<int, array<string, mixed>>, alerte: bool, message: string}
     */
    public function surveiller(bool $forcerAlerte = false, bool $leger = false): array
    {
        // En mode leger, on saute le controle de l'hebergement : il demande
        // une requete HTTP par serveur au panneau, soit plusieurs secondes
        // pendant lesquelles un processus PHP est immobilise. Acceptable en
        // ligne de commande, pas derriere chaque page servie a un visiteur.
        $controles = $this->controler($leger);

        $etatActuel = [];
        foreach ($controles as $controle) {
            $etatActuel[$controle['cle']] = $controle['ok'] ? 'ok' : 'ko';
        }

        $etatPrecedent = $this->lireEtat();

        $tombes = [];
        $revenus = [];
        foreach ($etatActuel as $cle => $valeur) {
            $avant = $etatPrecedent[$cle] ?? 'ok';
            if ('ko' === $valeur && 'ok' === $avant) {
                $tombes[] = $cle;
            } elseif ('ok' === $valeur && 'ko' === $avant) {
                $revenus[] = $cle;
            }
        }

        $this->ecrireEtat($etatActuel);

        if ([] === $tombes && [] === $revenus && !$forcerAlerte) {
            return ['controles' => $controles, 'alerte' => false, 'message' => 'Rien de neuf.'];
        }

        $message = $this->prevenir($controles, $tombes, $revenus);

        return ['controles' => $controles, 'alerte' => true, 'message' => $message];
    }

    // ------------------------------------------------- memoire sur disque

    /**
     * L'etat precedent est garde dans un fichier, PAS en base.
     *
     * C'est le point le plus important de tout ce service : une surveillance
     * qui a besoin de la base pour fonctionner se tait exactement le jour ou
     * la base tombe, c'est-a-dire le seul jour ou elle sert. Un fichier reste
     * lisible et inscriptible quoi qu'il arrive au serveur MySQL.
     */
    private function fichierEtat(): string
    {
        $dossier = $this->projectDir.'/var';
        if (!is_dir($dossier)) {
            @mkdir($dossier, 0775, true);
        }

        return $dossier.'/surveillance-etat.json';
    }

    /** @return array<string, string> */
    private function lireEtat(): array
    {
        $brut = @file_get_contents($this->fichierEtat());
        if (false === $brut || '' === $brut) {
            return [];
        }

        $etat = json_decode($brut, true);

        return is_array($etat) ? $etat : [];
    }

    /** @param array<string, string> $etat */
    private function ecrireEtat(array $etat): void
    {
        @file_put_contents($this->fichierEtat(), (string) json_encode($etat), LOCK_EX);
    }

    /** Date du dernier tour de controle, lue sur le fichier d'etat. */
    public function dernierPassage(): ?\DateTimeInterface
    {
        $date = @filemtime($this->fichierEtat());
        if (false === $date || 0 === $date) {
            return null;
        }

        return (new \DateTime())->setTimestamp($date);
    }

    /** Vrai si le prochain tour de controle est du. */
    public function estDu(int $intervalleSecondes): bool
    {
        $date = @filemtime($this->fichierEtat());
        if (false === $date) {
            return true;
        }

        return (time() - $date) >= $intervalleSecondes;
    }

    // ------------------------------------------------------------ controles

    /** @return array{cle: string, libelle: string, ok: bool, detail: string} */
    private function controlerBase(): array
    {
        try {
            $debut = microtime(true);
            $this->connexion->executeQuery('SELECT 1');
            $ms = (int) round((microtime(true) - $debut) * 1000);

            return $this->resultat('base', 'Base de donnees', true, sprintf('Repond en %d ms.', $ms));
        } catch (\Throwable $e) {
            return $this->resultat('base', 'Base de donnees', false, 'Injoignable : '.$e->getMessage());
        }
    }

    /** @return array{cle: string, libelle: string, ok: bool, detail: string} */
    private function controlerDisque(): array
    {
        $libre = @disk_free_space($this->projectDir);
        if (false === $libre || null === $libre) {
            return $this->resultat('disque', 'Espace disque', true, 'Non mesurable sur cet hebergement.');
        }

        $mo = (int) round($libre / 1048576);

        return $this->resultat(
            'disque',
            'Espace disque',
            $mo >= self::DISQUE_MINI_MO,
            sprintf('%d Mo libres (seuil : %d Mo).', $mo, self::DISQUE_MINI_MO)
        );
    }

    /** @return array{cle: string, libelle: string, ok: bool, detail: string} */
    private function controlerSauvegarde(): array
    {
        $age = $this->sauvegarde->ageDerniereEnHeures();
        if (null === $age) {
            return $this->resultat('sauvegarde', 'Sauvegarde', false, 'Aucune sauvegarde enregistree.');
        }

        return $this->resultat(
            'sauvegarde',
            'Sauvegarde',
            $age <= self::SAUVEGARDE_MAXI_HEURES,
            sprintf('La derniere date d\'il y a %s h (seuil : %d h).', $age, self::SAUVEGARDE_MAXI_HEURES)
        );
    }

    /** @return array{cle: string, libelle: string, ok: bool, detail: string} */
    private function controlerHebergement(): array
    {
        if (!$this->pterodactyl->isConfigured()) {
            return $this->resultat('hebergement', 'Hebergement', true, 'Panneau non configure : controle ignore.');
        }

        $serveurs = $this->pterodactyl->getServersWithUsage(20);
        if ([] === $serveurs) {
            return $this->resultat('hebergement', 'Hebergement', false, 'Le panneau ne repond pas ou ne renvoie aucun serveur.');
        }

        $enLigneMaintenant = [];
        foreach ($serveurs as $serveur) {
            $id = (string) ($serveur['identifier'] ?? '');
            if ('' !== $id && 'running' === ($serveur['status'] ?? null)) {
                $enLigneMaintenant[] = $id;
            }
        }

        // Le point important : un serveur eteint n'est PAS un incident. Sur un
        // panneau, la moitie des serveurs sont des bacs a sable arretes
        // volontairement. Exiger que tout tourne, c'est une alerte permanente,
        // donc une alerte que l'on finit par ne plus lire.
        //
        // Ce qui merite un e-mail, c'est un serveur qui tournait hier et qui
        // ne tourne plus aujourd'hui. On garde donc la liste de ceux qu'on a
        // deja vus en ligne, et on ne signale que les disparitions.
        $connus = $this->lireServeursConnus();
        $tombes = array_values(array_diff($connus, $enLigneMaintenant));

        // La reference s'enrichit des nouveaux, et oublie ceux qui restent
        // eteints : une fois signale, un serveur arrete devient le nouveau
        // normal et ne reveille plus personne.
        $this->ecrireServeursConnus($enLigneMaintenant);

        $eteints = count($serveurs) - count($enLigneMaintenant);

        if ([] !== $tombes) {
            return $this->resultat(
                'hebergement',
                'Hebergement',
                false,
                sprintf(
                    '%d serveur(s) qui tournaient se sont arretes : %s. %d en ligne sur %d.',
                    count($tombes),
                    implode(', ', array_slice($tombes, 0, 5)),
                    count($enLigneMaintenant),
                    count($serveurs)
                )
            );
        }

        return $this->resultat(
            'hebergement',
            'Hebergement',
            true,
            sprintf(
                '%d serveur(s) en ligne, %d arrete(s) volontairement. Aucun arret imprevu.',
                count($enLigneMaintenant),
                $eteints
            )
        );
    }

    /** @return string[] */
    private function lireServeursConnus(): array
    {
        $brut = @file_get_contents($this->projectDir.'/var/surveillance-serveurs.json');
        if (false === $brut || '' === $brut) {
            return [];
        }

        $liste = json_decode($brut, true);

        return is_array($liste) ? array_values(array_filter($liste, 'is_string')) : [];
    }

    /** @param string[] $identifiants */
    private function ecrireServeursConnus(array $identifiants): void
    {
        @file_put_contents(
            $this->projectDir.'/var/surveillance-serveurs.json',
            (string) json_encode(array_values($identifiants)),
            LOCK_EX
        );
    }

    /** @return array{cle: string, libelle: string, ok: bool, detail: string} */
    private function controlerEnvois(): array
    {
        try {
            $depuis = new \DateTime('-24 hours');
            $echecs = (int) $this->em->createQuery(
                'SELECT COUNT(n.id) FROM App\Entity\ProspectNote n
                 WHERE n.type = :type AND n.createdAt >= :depuis'
            )->setParameter('type', \App\Entity\ProspectNote::TYPE_EMAIL_ECHEC)
             ->setParameter('depuis', $depuis)
             ->getSingleScalarResult();

            return $this->resultat(
                'envois',
                'Envoi des e-mails',
                $echecs < 3,
                0 === $echecs
                    ? 'Aucun refus sur les 24 dernieres heures.'
                    : sprintf('%d envoi(s) refuse(s) sur 24 h.', $echecs)
            );
        } catch (\Throwable $e) {
            return $this->resultat('envois', 'Envoi des e-mails', true, 'Controle indisponible : '.$e->getMessage());
        }
    }

    /** @return array{cle: string, libelle: string, ok: bool, detail: string} */
    private function resultat(string $cle, string $libelle, bool $ok, string $detail): array
    {
        return ['cle' => $cle, 'libelle' => $libelle, 'ok' => $ok, 'detail' => $detail];
    }

    // -------------------------------------------------------------- alertes

    /**
     * @param array<int, array<string, mixed>> $controles
     * @param string[]                         $tombes
     * @param string[]                         $revenus
     */
    private function prevenir(array $controles, array $tombes, array $revenus): string
    {
        $titre = [] !== $tombes
            ? sprintf('Alerte walidbelbeche.fr : %s', implode(', ', $tombes))
            : sprintf('Retour a la normale : %s', implode(', ', $revenus));

        $lignes = [];
        foreach ($controles as $controle) {
            $lignes[] = sprintf('%s %s : %s', $controle['ok'] ? '[OK]' : '[KO]', $controle['libelle'], $controle['detail']);
        }
        $corps = implode("\n", $lignes);

        try {
            $this->mailer->send(
                (new Email())
                    ->from('contact@walidbelbeche.fr')
                    ->to('wbelbeche.s@gmail.com')
                    ->subject($titre)
                    ->text($corps."\n\nEtat complet : https://walidbelbeche.fr/admin/maintenance\n")
            );
        } catch (\Throwable $e) {
            error_log('[surveillance] e-mail impossible : '.$e->getMessage());
        }

        // Le push passe meme quand la messagerie est justement ce qui est
        // tombe : deux canaux valent mieux qu'un pour une alerte.
        try {
            foreach ($this->em->getRepository(PushSubscription::class)->findAll() as $abonnement) {
                $this->push->send($abonnement, [
                    'title' => $titre,
                    'body' => $lignes[0] ?? 'Voir le detail dans l\'administration.',
                    'url' => '/admin/maintenance',
                ]);
            }
        } catch (\Throwable $e) {
            error_log('[surveillance] push impossible : '.$e->getMessage());
        }

        return $titre;
    }
}
