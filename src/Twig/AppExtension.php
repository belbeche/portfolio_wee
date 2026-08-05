<?php

namespace App\Twig;

use App\Service\Situations;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // Ajout du filtre 'ucfirst' qui utilisera la fonction ucfirstCustom
            new TwigFilter('ucfirst', [$this, 'ucfirstCustom']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            // situations() : le catalogue technique traduit en phrases.
            new TwigFunction('situations', [Situations::class, 'toutes']),
            // aide_design('Design minimaliste') : la phrase qui rend un
            // libelle technique comprehensible, ou null.
            new TwigFunction('aide_design', [Situations::class, 'aideDesign']),
        ];
    }

    public function ucfirstCustom(string $string): string
    {
        // Mettre la première lettre en majuscule et le reste en minuscule
        return ucfirst(strtolower($string));
    }
}
