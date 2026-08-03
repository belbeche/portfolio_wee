<?php

namespace App\Twig;

use App\Security\Csp\CspNonceProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CspExtension extends AbstractExtension
{
    private CspNonceProvider $nonceProvider;

    public function __construct(CspNonceProvider $nonceProvider)
    {
        $this->nonceProvider = $nonceProvider;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('csp_nonce', [$this, 'nonce'], ['is_safe' => ['html_attr']]),
        ];
    }

    public function nonce(): string
    {
        return $this->nonceProvider->getNonce();
    }
}
