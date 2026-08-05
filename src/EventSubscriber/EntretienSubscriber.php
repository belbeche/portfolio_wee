<?php

namespace App\EventSubscriber;

use App\Service\Sauvegarde;
use App\Service\Surveillance;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * L'entretien automatique du site, sans tache planifiee.
 *
 * L'hebergement est un conteneur Pterodactyl : pas de cron, pas d'acces SSH.
 * On accroche donc l'entretien a kernel.terminate, c'est-a-dire APRES que la
 * reponse a ete envoyee au visiteur. Il a deja sa page, le serveur profite du
 * temps libre. Personne n'attend jamais derriere.
 *
 * Toute la cadence est gardee dans des fichiers, jamais en base. Une
 * surveillance qui a besoin de la base pour savoir quand se declencher se
 * tait exactement le jour ou la base tombe, c'est-a-dire le seul jour ou elle
 * sert a quelque chose.
 */
class EntretienSubscriber implements EventSubscriberInterface
{
    /** Une sauvegarde par jour. */
    private const SAUVEGARDE_TOUTES_LES = 86400;

    /** Un tour de controle toutes les quinze minutes. */
    private const SURVEILLANCE_TOUTES_LES = 900;

    private Sauvegarde $sauvegarde;
    private Surveillance $surveillance;
    private string $projectDir;

    public function __construct(Sauvegarde $sauvegarde, Surveillance $surveillance, string $projectDir)
    {
        $this->sauvegarde = $sauvegarde;
        $this->surveillance = $surveillance;
        $this->projectDir = $projectDir;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::TERMINATE => ['auTravail', -100]];
    }

    public function auTravail(TerminateEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $chemin = $event->getRequest()->getPathInfo();
        if (0 === strpos($chemin, '/api/') || 0 === strpos($chemin, '/build/') || 0 === strpos($chemin, '/src/')) {
            return;
        }

        try {
            $this->surveillanceSiDue();
            $this->sauvegardeSiDue();
        } catch (\Throwable $e) {
            // Journalise et se tait : le visiteur a deja sa page.
            error_log('[entretien] '.$e->getMessage());
        }
    }

    private function surveillanceSiDue(): void
    {
        if (!$this->surveillance->estDu(self::SURVEILLANCE_TOUTES_LES)) {
            return;
        }

        $verrou = $this->prendreLeVerrou('surveillance');
        if (null === $verrou) {
            return;
        }

        try {
            $this->surveillance->surveiller();
        } finally {
            $this->rendreLeVerrou($verrou);
        }
    }

    private function sauvegardeSiDue(): void
    {
        $temoin = $this->projectDir.'/var/derniere-tentative-sauvegarde';
        $derniereTentative = @filemtime($temoin);
        if (false !== $derniereTentative && (time() - $derniereTentative) < self::SAUVEGARDE_TOUTES_LES) {
            return;
        }

        $age = $this->sauvegarde->ageDerniereEnHeures();
        if (null !== $age && $age < 20) {
            return;
        }

        $verrou = $this->prendreLeVerrou('sauvegarde');
        if (null === $verrou) {
            return;
        }

        // Le temoin est pose AVANT de commencer. Si la sauvegarde echoue, on
        // ne la retente pas a chaque visite : ce serait le meilleur moyen de
        // saturer un serveur deja en difficulte. La surveillance signalera
        // l'absence de sauvegarde recente.
        @touch($temoin);

        try {
            @set_time_limit(300);
            $this->sauvegarde->creer();
        } finally {
            $this->rendreLeVerrou($verrou);
        }
    }

    /**
     * Verrou de fichier non bloquant : le premier processus passe, les autres
     * repartent immediatement au lieu d'attendre.
     *
     * @return resource|null
     */
    private function prendreLeVerrou(string $nom)
    {
        $dossier = $this->projectDir.'/var/verrous';
        if (!is_dir($dossier)) {
            @mkdir($dossier, 0775, true);
        }

        $fichier = @fopen($dossier.'/'.$nom.'.lock', 'c');
        if (false === $fichier) {
            return null;
        }

        if (!flock($fichier, LOCK_EX | LOCK_NB)) {
            fclose($fichier);

            return null;
        }

        return $fichier;
    }

    /** @param resource $verrou */
    private function rendreLeVerrou($verrou): void
    {
        @flock($verrou, LOCK_UN);
        @fclose($verrou);
    }
}
