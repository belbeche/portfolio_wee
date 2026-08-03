<?php

namespace App\Controller\Back;

use App\Service\Settings;
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

        return $this->redirectToRoute('back_settings');
    }
}
