<?php

namespace App\Security\Csp;

/**
 * Fournit un nonce unique par requête, partagé entre l'en-tête CSP
 * et les balises <script> / <style> des templates.
 *
 * Usage dans Twig :   <script nonce="{{ csp_nonce() }}"> ... </script>
 */
final class CspNonceProvider
{
    private ?string $nonce = null;

    public function getNonce(): string
    {
        if (null === $this->nonce) {
            $this->nonce = base64_encode(random_bytes(16));
        }

        return $this->nonce;
    }

    public function reset(): void
    {
        $this->nonce = null;
    }
}
