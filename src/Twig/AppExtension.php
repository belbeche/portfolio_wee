<?php

namespace App\Twig;

use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

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

    public function getFunctions()
    {
        return [
            new TwigFunction('get_fr_translations', [$this, 'getFrTranslations']),
            new TwigFunction('get_en_translations', [$this, 'getEnTranslations']),
        ];
    }

    public function getFrTranslations()
    {
        return json_encode([
            'hero_title' => 'Développeur Full Stack',
            // Ajoutez TOUTES vos clés françaises
        ]);
    }

    public function getEnTranslations()
    {
        return json_encode([
            'hero_title' => 'Full Stack Developer',
            // Ajoutez TOUTES vos clés anglaises
        ]);
    }
}
