<?php

namespace App\Controller\Back;

use App\Entity\PushSubscription;
use App\Service\Settings;
use App\Service\WebPush;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Parametres de l'application, modifiables sans deploiement.
 *
 * @Route("/admin/parametres")
 * @IsGranted("ROLE_ADMIN")
 */
class SettingController extends AbstractController
{
    /**
     * @Route("", name="back_settings", methods={"GET", "POST"})
     */
    public function index(Request $request, Settings $settings): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('app_settings', (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Jeton de sécurité invalide.');

                return $this->redirectToRoute('back_settings');
            }

            $saved = 0;
            foreach (Settings::CATALOG as $name => $meta) {
                if (!$request->request->has($name)) {
                    continue;
                }
                $value = trim((string) $request->request->get($name));

                // Champ secret laisse vide = on garde la valeur en place.
                if ($meta['secret'] && '' === $value) {
                    continue;
                }

                if ($value !== (string) $settings->getStored($name)) {
                    $settings->set($name, '' === $value ? null : $value);
                    ++$saved;
                }
            }

            $this->addFlash('success', $saved > 0
                ? sprintf('%d paramètre(s) enregistré(s). Effet immédiat, sans redéploiement.', $saved)
                : 'Aucune modification.');

            return $this->redirectToRoute('back_settings');
        }

        $rows = [];
        foreach (Settings::CATALOG as $name => $meta) {
            $stored = $settings->getStored($name);
            $effective = $settings->get($name);
            $rows[] = [
                'name' => $name,
                'label' => $meta['label'],
                'help' => $meta['help'],
                'secret' => $meta['secret'],
                'stored' => $stored,
                'hasValue' => null !== $effective && '' !== $effective,
                'fromEnv' => (null === $stored || '' === $stored) && null !== $effective && '' !== $effective,
            ];
        }

        return $this->render('back/setting/index.html.twig', [
            'rows' => $rows,
        ]);
    }

    /**
     * Force l'actualisation chez tous les visiteurs : incremente la version
     * des fichiers statiques, donc les adresses CSS et scripts changent et
     * les caches navigateurs comme Cloudflare vont rechercher les fichiers.
     *
     * @Route("/purger-cache", name="back_settings_purge", methods={"POST"})
     */
    public function purgeCache(Request $request, Settings $settings): Response
    {
        if (!$this->isCsrfTokenValid('settings_purge', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expiree, reessaie.');

            return $this->redirectToRoute('back_settings');
        }

        $version = (int) $settings->get('asset_version', '12');
        $settings->set('asset_version', (string) ($version + 1));

        $this->addFlash('success', sprintf(
            'Version des fichiers statiques passee a %d. Tous les visiteurs rechargeront les derniers CSS et scripts a leur prochaine visite.',
            $version + 1
        ));

        // rechargement=1 declenche, au retour, la purge du cache du navigateur
        // et du service worker, puis un rechargement force de la page.
        return $this->redirectToRoute('back_settings', ['rechargement' => 1]);
    }

    /**
     * Envoie une notification d'essai a tous les navigateurs abonnes.
     *
     * @Route("/push-essai", name="back_settings_push_test", methods={"POST"})
     */
    public function pushTest(Request $request, WebPush $webPush, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('settings_push_test', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expiree, reessaie.');

            return $this->redirectToRoute('back_settings');
        }

        $abonnements = $em->getRepository(PushSubscription::class)->findAll();
        if ([] === $abonnements) {
            $this->addFlash('info', "Aucun navigateur abonne pour l'instant. Clique « Activer les notifications » sur l'espace client, puis reviens ici.");

            return $this->redirectToRoute('back_settings');
        }

        $envoyes = 0;
        $erreurs = [];
        foreach ($abonnements as $abonnement) {
            $erreur = $webPush->send($abonnement, [
                'title' => 'walidbelbeche.fr',
                'body' => "Notification d'essai : le canal push fonctionne.",
                'url' => '/espace',
            ]);
            if (null === $erreur) {
                ++$envoyes;
            } else {
                $erreurs[] = $erreur;
            }
        }

        if ($envoyes > 0) {
            $this->addFlash('success', sprintf('%d notification(s) d\'essai envoyee(s) sur %d abonnement(s).', $envoyes, count($abonnements)));
        }
        foreach (array_slice(array_unique($erreurs), 0, 3) as $e) {
            $this->addFlash('error', 'Push : '.$e);
        }

        return $this->redirectToRoute('back_settings');
    }

    /**
     * Vide le cache du serveur sans passer par la console.
     *
     * Cible volontairement les deux caches qui posent probleme au quotidien :
     * les gabarits Twig compiles et les traductions. Le cache du conteneur
     * de services n'est PAS touche : le supprimer pendant qu'une requete
     * l'utilise casserait le site jusqu'au prochain redemarrage.
     *
     * @Route("/vider-cache-serveur", name="back_settings_clear_cache", methods={"POST"})
     */
    public function clearServerCache(Request $request, KernelInterface $kernel, Settings $settings): Response
    {
        if (!$this->isCsrfTokenValid('settings_clear_cache', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expiree, reessaie.');

            return $this->redirectToRoute('back_settings');
        }

        $racine = $kernel->getCacheDir();
        $supprimes = 0;
        $erreurs = [];

        foreach (['twig', 'translations', 'pools'] as $dossier) {
            $chemin = $racine.'/'.$dossier;
            if (!is_dir($chemin)) {
                continue;
            }

            $fichiers = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($chemin, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($fichiers as $fichier) {
                try {
                    if ($fichier->isDir()) {
                        @rmdir($fichier->getPathname());
                    } elseif (@unlink($fichier->getPathname())) {
                        ++$supprimes;
                    }
                } catch (\Throwable $e) {
                    $erreurs[] = $e->getMessage();
                }
            }
        }

        // La version des fichiers statiques change aussi : les visiteurs
        // rechargent CSS et scripts dans la foulee.
        $version = (int) $settings->get('asset_version', '12');
        $settings->set('asset_version', (string) ($version + 1));

        $this->addFlash('success', sprintf(
            '%d fichier(s) de cache supprime(s) : gabarits, traductions et donnees mises en cache. Version des fichiers statiques passee a %d. Recharge la page pour voir le resultat.',
            $supprimes,
            $version + 1
        ));

        if ([] !== $erreurs) {
            $this->addFlash('error', 'Certains fichiers n\'ont pas pu etre supprimes : '.implode(' | ', array_slice(array_unique($erreurs), 0, 2)));
        }

        // Vider le cache du serveur ne sert a rien si le navigateur, lui,
        // ressert sa copie. rechargement=1 fait purger le cache du navigateur
        // et le service worker au retour, puis recharge la page.
        return $this->redirectToRoute('back_settings', ['rechargement' => 1]);
    }
}
