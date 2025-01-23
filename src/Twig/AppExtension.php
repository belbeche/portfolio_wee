<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // Ajout du filtre 'ucfirst' qui utilisera la fonction ucfirstCustom
            new TwigFilter('ucfirst', [$this, 'ucfirstCustom']),
        ];
    }

    public function ucfirstCustom(string $string): string
    {
        // Mettre la première lettre en majuscule et le reste en minuscule
        return ucfirst(strtolower($string));
    }
}
