<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Relais vers AxisHumain.
 *
 * Un indépendant ne peut pas tout prendre : il y a les projets hors périmètre,
 * ceux hors budget, et ceux qui arrivent quand le planning est déjà plein.
 * Jusqu'ici ces demandes se terminaient par un refus, c'est-à-dire par rien.
 *
 * Elles sont désormais orientées vers AxisHumain, qui met en relation les
 * entreprises avec des experts IT. Le prospect repart avec une solution,
 * la plateforme gagne une mission, et le lien reste ouvert pour plus tard.
 */
class ReferralController extends AbstractController
{
    /**
     * Page de relais, cible de tous les boutons « ce projet sort de mon périmètre ».
     *
     * @Route("/besoin-expert", name="front_referral")
     */
    public function index(): Response
    {
        return $this->render('front/referral/index.html.twig');
    }

    /**
     * Redirection tracée vers AxisHumain.
     *
     * Passer par une route interne plutôt que par un lien direct permet de
     * compter les renvois : sans mesure, impossible de savoir si le relais
     * sert à quelque chose.
     *
     * @Route("/vers-axishumain/{context}", name="front_referral_go", requirements={"context"="[a-z0-9_-]{1,40}"})
     */
    public function go(\App\Service\Settings $settings, \Symfony\Component\HttpFoundation\Request $request, string $context = 'portfolio'): RedirectResponse
    {
        // L'URL vient des parametres administrables, avec un defaut sur :
        // meme sans aucune configuration, le lien fonctionne.
        $base = rtrim((string) $settings->get('axishumain_url', 'https://axishumain.fr'), '/');

        // ?profil=client ou ?profil=expert oriente vers la bonne inscription.
        $profil = (string) $request->query->get('profil', '');
        $path = '';
        if ('client' === $profil) {
            $path = (string) $settings->get('axishumain_client_path', '');
        } elseif ('expert' === $profil) {
            $path = (string) $settings->get('axishumain_expert_path', '');
        }

        // Le reglage peut contenir soit un chemin (/premium), soit une adresse
        // complete (https://axishumain.fr/premium). Dans le second cas il ne
        // faut surtout pas la coller derriere l'adresse de base, sinon on
        // obtient axishumain.frhttps://axishumain.fr/premium.
        if ('' !== $path && preg_match('#^https?://#i', $path)) {
            $cible = rtrim($path, '/');
        } else {
            $path = '' === $path ? '' : '/'.ltrim($path, '/');
            $cible = $base.$path;
        }

        $separator = str_contains($cible, '?') ? '&' : '?';

        $url = sprintf(
            '%s%sutm_source=walidbelbeche&utm_medium=referral&utm_campaign=%s',
            $cible,
            $separator,
            urlencode($context)
        );

        return $this->redirect($url);
    }
}
