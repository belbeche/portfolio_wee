<?php

// src/EventListener/LocaleListener.php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleListener implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        // Si la locale est définie dans l'URL, on la stocke dans la session
        if ($locale = $request->query->get('locale')) {
            $request->getSession()->set('_locale', $locale);
        }

        // On récupère la locale dans la session (si disponible) et on la définit
        if ($locale = $request->getSession()->get('_locale')) {
            $request->setLocale($locale);
        }
    }

    public static function getSubscribedEvents()
    {
        // Événement de Symfony à écouter
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
