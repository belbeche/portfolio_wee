<?php

namespace App\EventSubscriber;

use App\Controller\Front\LocaleController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Applique a chaque requete la langue choisie par le visiteur.
 *
 * Priorite 17 : juste au-dessus du LocaleListener de Symfony (16), de sorte
 * qu'une route portant explicitement un parametre _locale continue de gagner.
 * C'est ce qui permet a /change-locale/{_locale} de fonctionner sans que la
 * session en cours vienne ecraser la langue demandee.
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'fr')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        $locale = (string) $request->getSession()->get('_locale', $this->defaultLocale);

        if (!in_array($locale, LocaleController::SUPPORTED_LOCALES, true)) {
            $locale = $this->defaultLocale;
        }

        $request->setLocale($locale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 17]],
        ];
    }
}
