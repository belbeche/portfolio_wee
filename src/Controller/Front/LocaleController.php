<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Changement de langue.
 *
 * Deux corrections par rapport a la version precedente :
 *
 * 1. La langue demandee est validee contre une liste fermee. Sans ce
 *    controle, n'importe quelle valeur passee dans l'URL etait ecrite
 *    telle quelle en session.
 *
 * 2. La redirection vers le referer est verifiee. Rediriger sans controle
 *    vers l'en-tete Referer est une redirection ouverte : il suffisait
 *    d'envoyer quelqu'un sur /change-locale/fr depuis un site tiers pour
 *    le renvoyer ensuite ou l'on voulait, en partant d'un lien portant
 *    ton nom de domaine.
 */
class LocaleController extends AbstractController
{
    public const SUPPORTED_LOCALES = ['fr', 'en'];
    public const COOKIE_NAME = 'wb_locale';

    /**
     * @Route("/change-locale/{_locale}", name="switch_locale", requirements={"_locale"="fr|en"})
     */
    public function switchLocale(string $_locale, Request $request): RedirectResponse
    {
        if (!in_array($_locale, self::SUPPORTED_LOCALES, true)) {
            $_locale = self::SUPPORTED_LOCALES[0];
        }

        $request->getSession()->set('_locale', $_locale);

        $response = new RedirectResponse($this->safeReferer($request));

        // Cookie d'un an : la preference survit a la perte de session
        // (session expiree, passage www/apex, navigateur qui purge).
        $response->headers->setCookie(Cookie::create(
            self::COOKIE_NAME,
            $_locale,
            new \DateTimeImmutable('+1 year'),
            '/',
            null,
            $request->isSecure(),
            false, // lisible cote client si besoin d'affichage
            false,
            Cookie::SAMESITE_LAX
        ));

        return $response;
    }

    /**
     * Ne renvoie le referer que s'il pointe vers ce site.
     */
    private function safeReferer(Request $request): string
    {
        $referer = (string) $request->headers->get('referer');

        if ('' === $referer) {
            return $this->generateUrl('front_home');
        }

        $host = parse_url($referer, PHP_URL_HOST);

        if (null === $host || $host === $request->getHost()) {
            return $referer;
        }

        return $this->generateUrl('front_home');
    }
}
