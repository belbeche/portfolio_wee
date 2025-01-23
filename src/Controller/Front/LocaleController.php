<?php

namespace App\Controller\Front;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LocaleController extends AbstractController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/change-locale/{_locale}", name="switch_locale")
     *
     * @param string $_locale
     * @param Request $request
     * @return RedirectResponse
     */
    public function switchLocale(string $_locale, Request $request): RedirectResponse
    {
        // Met à jour la langue dans la session
        $this->requestStack->getSession()->set('_locale', $_locale);

        // Récupère l'URL de référence (HTTP referer)
        $referer = $request->headers->get('referer');

        // Si l'URL de référence existe, redirige l'utilisateur vers celle-ci
        if ($referer) {
            return new RedirectResponse($referer);
        }

        // Si aucune URL de référence, redirige vers la racine
        return new RedirectResponse('/');
    }
}
