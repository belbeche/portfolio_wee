<?php

namespace App\EventSubscriber;

use App\Service\Sauvegarde;
use App\Service\Settings;
use App\Service\Surveillance;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * L'entretien automatique du site, sans tache planifiee.
 *
 * L'hebergement est un conteneur Pterodactyl : pas de cron, pas d'acces SSH.
 * On accroche donc l'entretien a kernel.terminate, c'est-a-dire APRES que la
 * reponse a ete envoyee au visiteur. Concretement : le visiteur a deja sa
 * page, et le serveur profite de ce temps libre pour sauvegarder ou
 * controler. Personne n'attend jamais derriere.
 *
 * Trois garde-fous, parce qu'un entretien qui casse le site est pire que pas
 * d'entretien du tout :
 *   - une cadence stockee en base, donc partagee par tous les processus
 *   - un fichier verrou, pour que deux visiteurs simultanes ne lancent pas
 *     deux sauvegardes en meme temps
 *   - un try/catch total : une panne de l'entretien reste invisible du site
 */
class EntretienSubscriber implements EventSubscriberInterface
{
    /** Une sauvegarde par jour. */
    private const SAUVEGARDE_TOUTES_LES = 86400;

    /** Un tour de controle toutes les quinze minutes. */
    private const SURVEILLANCE_TOUTES_LES = 900;

    private Settings $settings;
    private Sauvegarde $sauvegarde;
    private Surveillance $surveillance;
    private string $projectDir;

    public function __construct(Settings $settings, Sauvegarde $sauvegarde, Surveillance $surveillance, string $projectDir)
    {
        $this->settings = $settings;
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

        // Les appels d'entretien du site lui-meme ne doivent pas se relancer.
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
        if (!$this->estDu('surveillance_dernier_passage', self::SURVEILLANCE_TOUTES_LES)) {
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
        if (!$this->estDu('sauvegarde_dernier_passage', self::SAUVEGARDE_TOUTES_LES)) {
            return;
        }

        $verrou = $this->prendreLeVerrou('sauvegarde');
        if (null === $verrou) {
            return;
        }

        // La date est posee AVANT de commencer : si la sauvegarde echoue, on
        // ne la retente pas a chaque visite, ce qui saturerait le serveur.
        // La surveillance signalera l'absence de sauvegarde recente.
        $this->settings->set('sauvegarde_dernier_passage', (string) time());

        try {
            @set_time_limit(300);
            $this->sauvegarde->creer();
        } finally {
            $this->rendreLeVerrou($verrou);
        }
    }

    private function estDu(string $cle, int $intervalle): bool
    {
        $dernier = (int) $this->settings->get($cle, '0');

        return (time() - $dernier) >= $intervalle;
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
