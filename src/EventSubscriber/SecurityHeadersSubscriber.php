<?php

namespace App\EventSubscriber;

use App\Security\Csp\CspNonceProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Ajoute les en-têtes de sécurité HTTP à chaque réponse HTML.
 *
 * Deux modes de CSP, pilotés par les variables d'environnement :
 *
 *   CSP_ENFORCE=0  -> en-tête Content-Security-Policy-Report-Only
 *                     (la page fonctionne, les violations sont juste signalées
 *                      dans la console du navigateur). Mode de mise au point.
 *   CSP_ENFORCE=1  -> en-tête Content-Security-Policy (bloquant).
 *
 *   CSP_STRICT=0   -> 'unsafe-inline' autorisé pour les scripts.
 *                     Nécessaire tant que les templates contiennent des
 *                     balises <script> en ligne sans attribut nonce.
 *   CSP_STRICT=1   -> plus d'unsafe-inline : chaque <script> en ligne doit
 *                     porter nonce="{{ csp_nonce() }}". C'est la cible.
 */
final class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    private CspNonceProvider $nonceProvider;
    private bool $cspEnforce;
    private bool $cspStrict;
    private string $environment;
    private ?string $reportUri;

    public function __construct(
        CspNonceProvider $nonceProvider,
        bool $cspEnforce = false,
        bool $cspStrict = false,
        string $environment = 'prod',
        ?string $reportUri = null
    ) {
        $this->nonceProvider = $nonceProvider;
        $this->cspEnforce = $cspEnforce;
        $this->cspStrict = $cspStrict;
        $this->environment = $environment;
        $this->reportUri = $reportUri ?: null;
    }

    public static function getSubscribedEvents(): array
    {
        // Priorité négative : on passe après les autres listeners de réponse.
        return [KernelEvents::RESPONSE => ['onKernelResponse', -128]];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        // Ne jamais toucher au profiler ni à la toolbar de debug :
        // ils utilisent massivement du style et du script en ligne.
        $route = (string) $request->attributes->get('_route');
        if (str_starts_with($route, '_wdt') || str_starts_with($route, '_profiler')) {
            return;
        }

        $headers = $response->headers;

        // --- En-têtes valables pour toutes les réponses -------------------
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('X-Frame-Options', 'DENY');
        // Le filtre XSS historique des navigateurs crée plus de failles qu'il
        // n'en corrige : la recommandation actuelle est de le désactiver et de
        // s'appuyer sur la CSP.
        $headers->set('X-XSS-Protection', '0');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $headers->set(
            'Permissions-Policy',
            'accelerometer=(), autoplay=(), camera=(), display-capture=(), '
            .'encrypted-media=(), fullscreen=(self), geolocation=(), gyroscope=(), '
            .'magnetometer=(), microphone=(), midi=(), payment=(), usb=()'
        );

        // HSTS : uniquement en HTTPS et hors environnement de développement,
        // sinon on verrouille le navigateur sur du http://localhost.
        if ($request->isSecure() && 'dev' !== $this->environment) {
            $headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // --- CSP : uniquement sur les réponses HTML -----------------------
        $contentType = (string) $headers->get('Content-Type', '');
        if ('' !== $contentType && !str_contains($contentType, 'text/html')) {
            return;
        }

        $headerName = $this->cspEnforce
            ? 'Content-Security-Policy'
            : 'Content-Security-Policy-Report-Only';

        $headers->set($headerName, $this->buildPolicy());
    }

    private function buildPolicy(): string
    {
        // Hôtes réellement utilisés par les templates du projet.
        $cdn = "https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://code.jquery.com";
        $analytics = 'https://stats.youss.dev';
        $fonts = 'https://fonts.googleapis.com https://fonts.gstatic.com';

        if ($this->cspStrict) {
            // Cible : nonce + strict-dynamic, plus aucun script en ligne libre.
            $scriptSrc = sprintf(
                "'self' 'nonce-%s' 'strict-dynamic' %s %s",
                $this->nonceProvider->getNonce(),
                $cdn,
                $analytics
            );
        } else {
            // Transition : les <script> en ligne existants continuent de
            // fonctionner. La CSP bloque déjà tout script provenant d'un
            // domaine non listé, ce qui couvre l'essentiel des injections.
            $scriptSrc = sprintf("'self' 'unsafe-inline' %s %s", $cdn, $analytics);
        }

        $directives = [
            "default-src 'self'",
            'script-src '.$scriptSrc,
            // Bootstrap et les composants existants posent des styles en ligne :
            // 'unsafe-inline' reste nécessaire ici et le risque est faible.
            sprintf("style-src 'self' 'unsafe-inline' %s %s", $cdn, $fonts),
            sprintf("font-src 'self' data: %s %s", $cdn, $fonts),
            "img-src 'self' data: https:",
            // recherche-entreprises : l'annuaire public des entreprises (SIREN)
            sprintf("connect-src 'self' https://recherche-entreprises.api.gouv.fr %s", $analytics),
            'frame-src https://calendly.com https://*.calendly.com https://www.google.com',
            "media-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ];

        // Ignoree (et signalee) par les navigateurs en Report-Only :
        // on ne l'ajoute qu'en mode application.
        if ($this->cspEnforce) {
            $directives[] = 'upgrade-insecure-requests';
        }

        if (null !== $this->reportUri) {
            $directives[] = 'report-uri '.$this->reportUri;
        }

        return implode('; ', $directives);
    }
}
