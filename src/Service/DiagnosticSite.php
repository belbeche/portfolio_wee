<?php

namespace App\Service;

use App\Entity\Prospect;

/**
 * Regarde le site d'un prospect et en tire des constats verifiables.
 *
 * C'est le coeur de la difference entre deux e-mails :
 *
 *   « Bonjour, je developpe des sites, avez-vous un besoin ? »
 *   « Bonjour, votre site s'ouvre en http : Chrome affiche Non securise a
 *     vos visiteurs, et Google le declasse depuis 2018. »
 *
 * Le premier est une sollicitation, le second est une information utile que
 * le destinataire ne connaissait peut-etre pas. Meme service vendu derriere,
 * taux de reponse sans commune mesure.
 *
 * Regle absolue : on n'invente rien. Chaque constat repose sur une mesure
 * faite sur la page. Un constat faux detruit la credibilite plus surement
 * qu'un e-mail generique.
 */
class DiagnosticSite
{
    private const DELAI = 8;
    private const LENT_MS = 2500;

    /**
     * Analyse le site du prospect et retourne le diagnostic.
     *
     * @return array<string, mixed>
     */
    public function analyser(Prospect $prospect): array
    {
        return $this->analyserUrl((string) $prospect->getWebsite());
    }

    /**
     * La meme analyse, a partir d'une simple adresse.
     *
     * L'audit public et la prospection partagent ce moteur : un constat qui
     * s'affiche a un visiteur est exactement celui qui partira dans un e-mail,
     * il n'y a pas deux verites.
     *
     * @return array<string, mixed>
     */
    public function analyserUrl(string $adresse): array
    {
        $url = $this->normaliser($adresse);

        if ('' === $url) {
            return [
                'url' => null,
                'joignable' => false,
                'score' => null,
                'notes' => [],
                'constats' => [],
                'resume' => 'Aucune adresse de site connue.',
            ];
        }

        $mesure = $this->charger($url);

        if (!$mesure['joignable']) {
            return [
                'url' => $url,
                'joignable' => false,
                'score' => 0,
                'notes' => ['securite' => 0, 'performance' => 0, 'mobile' => 0, 'seo' => 0, 'global' => 0],
                'constats' => [[
                    'cle' => 'injoignable',
                    'titre' => 'Le site ne répond pas',
                    'phrase' => sprintf("votre site %s ne répond pas depuis l'extérieur", $this->hote($url)),
                    'gravite' => 'haute',
                ]],
                'resume' => 'Site injoignable au moment du test.',
            ];
        }

        $html = $mesure['corps'];
        $constats = [];

        // --- 1. HTTPS ------------------------------------------------------
        if (!$mesure['https']) {
            $constats[] = [
                'cle' => 'https',
                'titre' => 'Pas de certificat',
                'phrase' => "votre site s'ouvre en http : les navigateurs affichent « Non sécurisé » à vos visiteurs, et Google déclasse ces pages depuis 2018",
                'gravite' => 'haute',
            ];
        }

        // --- 2. Adapte au mobile ------------------------------------------
        if (!preg_match('/<meta[^>]+name=["\']viewport["\']/i', $html)) {
            $constats[] = [
                'cle' => 'mobile',
                'titre' => 'Pas prévu pour le mobile',
                'phrase' => "votre page n'a pas de balise viewport : sur un téléphone elle s'affiche en miniature, alors que la majorité de vos visiteurs arrivent de là",
                'gravite' => 'haute',
            ];
        }

        // --- 3. Vitesse ----------------------------------------------------
        if ($mesure['ms'] > self::LENT_MS) {
            $constats[] = [
                'cle' => 'vitesse',
                'titre' => 'Page lente',
                'phrase' => sprintf(
                    'votre page met %.1f seconde(s) à répondre depuis un accès standard : au-delà de deux secondes, une part importante des visiteurs repart avant de voir quoi que ce soit',
                    $mesure['ms'] / 1000
                ),
                'gravite' => 'haute',
            ];
        }

        // --- 4. Description pour les moteurs -------------------------------
        if (!preg_match('/<meta[^>]+name=["\']description["\'][^>]*content=["\'][^"\']{20,}/i', $html)) {
            $constats[] = [
                'cle' => 'description',
                'titre' => 'Pas de description',
                'phrase' => "votre page n'a pas de meta description : dans les résultats Google, le texte affiché sous votre titre est choisi au hasard dans la page",
                'gravite' => 'moyenne',
            ];
        }

        // --- 5. Titre de page ----------------------------------------------
        if (preg_match('/<title>(.*?)<\/title>/is', $html, $m)) {
            $titre = trim(html_entity_decode(strip_tags($m[1])));
            if ('' === $titre || mb_strlen($titre) < 12) {
                $constats[] = [
                    'cle' => 'titre',
                    'titre' => 'Titre de page trop court',
                    'phrase' => sprintf("le titre de votre page est « %s » : c'est ce que Google affiche en bleu, et c'est ce qui décide du clic", $titre),
                    'gravite' => 'moyenne',
                ];
            }
        } else {
            $constats[] = [
                'cle' => 'titre',
                'titre' => 'Aucun titre de page',
                'phrase' => "votre page n'a pas de balise title : Google affiche alors l'adresse brute dans ses résultats",
                'gravite' => 'haute',
            ];
        }

        // --- 6. Signe de site laisse de cote --------------------------------
        $anneeCourante = (int) date('Y');
        if (preg_match_all('/(?:©|&copy;|Copyright)[^0-9]{0,12}(20[0-2][0-9])/i', $html, $m)) {
            $derniere = max(array_map('intval', $m[1]));
            if ($derniere > 0 && $derniere <= $anneeCourante - 2) {
                $constats[] = [
                    'cle' => 'abandon',
                    'titre' => 'Site figé',
                    'phrase' => sprintf(
                        'le bas de votre page affiche encore %d : un visiteur en déduit que le site, et parfois l\'entreprise, ne sont plus suivis',
                        $derniere
                    ),
                    'gravite' => 'moyenne',
                ];
            }
        }

        // --- 7. Poids de la page --------------------------------------------
        $poidsKo = (int) round(strlen($html) / 1024);
        if ($poidsKo > 900) {
            $constats[] = [
                'cle' => 'poids',
                'titre' => 'Page très lourde',
                'phrase' => sprintf('le seul code de votre page pèse %d Ko avant même les images : sur un forfait mobile, cela se paie en secondes d\'attente', $poidsKo),
                'gravite' => 'moyenne',
            ];
        }

        // Les constats les plus graves d'abord : le premier e-mail n'en cite
        // qu'un, autant que ce soit celui qui parle le plus.
        usort($constats, static fn ($a, $b) => ('haute' === $b['gravite'] ? 1 : 0) <=> ('haute' === $a['gravite'] ? 1 : 0));

        $notes = $this->noter($mesure, $constats, $poidsKo);

        return [
            'url' => $url,
            'joignable' => true,
            'https' => $mesure['https'],
            'ms' => $mesure['ms'],
            'poidsKo' => $poidsKo,
            'notes' => $notes,
            'score' => $notes['global'],
            'constats' => $constats,
            'resume' => [] === $constats
                ? 'Rien de bloquant repéré : ce site est correctement tenu.'
                : sprintf('%d point(s) repéré(s), dont %d important(s).',
                    count($constats),
                    count(array_filter($constats, static fn ($c) => 'haute' === $c['gravite']))),
        ];
    }

    /**
     * Les quatre notes et la note globale, sur 100.
     *
     * Le bareme est volontairement simple et explicable : un visiteur qui voit
     * « Securite 30 » doit pouvoir comprendre pourquoi en lisant les constats
     * juste en dessous. Une note qu'on ne sait pas justifier ne convainc
     * personne, et se retourne contre celui qui l'affiche.
     *
     * @param array{joignable: bool, https: bool, ms: int, corps: string} $mesure
     * @param array<int, array<string, string>>                           $constats
     *
     * @return array{securite: int, performance: int, mobile: int, seo: int, global: int}
     */
    private function noter(array $mesure, array $constats, int $poidsKo): array
    {
        $cles = array_column($constats, 'cle');
        $a = static fn (string $cle): bool => in_array($cle, $cles, true);

        // Securite : le certificat vaut l'essentiel de la note. Un site en
        // http n'est pas « un peu moins sur », il est signale comme non sur
        // a chacun de ses visiteurs.
        $securite = $mesure['https'] ? 100 : 30;

        // Performance : deux secondes est le seuil ou l'abandon decolle.
        $ms = max(1, $mesure['ms']);
        if ($ms <= 800) {
            $performance = 100;
        } elseif ($ms <= 1500) {
            $performance = 85;
        } elseif ($ms <= 2500) {
            $performance = 65;
        } elseif ($ms <= 4000) {
            $performance = 40;
        } else {
            $performance = 20;
        }
        if ($poidsKo > 900) {
            $performance = max(10, $performance - 15);
        }

        // Mobile : la balise viewport est binaire, on l'a ou on ne l'a pas.
        $mobile = $a('mobile') ? 25 : 100;

        // Referencement : trois points qui se voient directement dans les
        // resultats de recherche.
        $seo = 100;
        if ($a('description')) {
            $seo -= 30;
        }
        if ($a('titre')) {
            $seo -= 30;
        }
        if ($a('abandon')) {
            $seo -= 15;
        }
        if (!$mesure['https']) {
            $seo -= 10; // Google declasse les pages en http depuis 2018
        }
        $seo = max(0, $seo);

        // La note globale pese ce qui coute le plus cher a negliger.
        $global = (int) round(
            $securite * 0.30
            + $performance * 0.30
            + $mobile * 0.20
            + $seo * 0.20
        );

        return [
            'securite' => $securite,
            'performance' => $performance,
            'mobile' => $mobile,
            'seo' => $seo,
            'global' => $global,
        ];
    }

    /** @return array{joignable: bool, https: bool, ms: int, corps: string} */
    private function charger(string $url): array
    {
        $debut = microtime(true);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 4,
            CURLOPT_TIMEOUT => self::DELAI,
            CURLOPT_CONNECTTIMEOUT => 5,
            // Un User-Agent honnete : on ne se fait pas passer pour un
            // navigateur, et on laisse une adresse pour etre contacte.
            CURLOPT_USERAGENT => 'WalidBelbecheBot/1.0 (+https://walidbelbeche.fr)',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $corps = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $finale = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        $ms = (int) round((microtime(true) - $debut) * 1000);

        return [
            'joignable' => false !== $corps && $code > 0 && $code < 400,
            'https' => 0 === stripos($finale, 'https://'),
            'ms' => $ms,
            'corps' => is_string($corps) ? $corps : '',
        ];
    }

    private function normaliser(string $url): string
    {
        $url = trim($url);
        if ('' === $url) {
            return '';
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
    }

    private function hote(string $url): string
    {
        return (string) (parse_url($url, PHP_URL_HOST) ?: $url);
    }
}
