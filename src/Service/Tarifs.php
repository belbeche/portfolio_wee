<?php

namespace App\Service;

/**
 * Le bareme public de l'estimateur.
 *
 * Deux partis pris qui gouvernent ce fichier.
 *
 * Le premier : afficher des prix. Beaucoup de prestataires les cachent pour
 * « ne pas se fermer de portes ». En pratique, un visiteur qui ne trouve aucun
 * prix suppose que c'est cher, et il part. Celui qui voit une fourchette
 * arrive au premier echange en sachant deja s'il est dans le budget, et cet
 * echange-la vaut dix demandes vagues.
 *
 * Le second : annoncer une fourchette, jamais un prix ferme. Un chiffre unique
 * affiche avant d'avoir parle du projet devient une promesse que l'on ne peut
 * pas tenir, et le rattraper plus tard abime la confiance bien plus que
 * l'imprecision de depart.
 */
class Tarifs
{
    /** Amplitude de la fourchette affichee, de part et d'autre du total. */
    public const MARGE = 0.2;

    /**
     * Taux journalier de repli, si le reglage n'est pas encore renseigne.
     *
     * La premiere version de ce fichier posait des prix ronds au jugé. En les
     * divisant par le nombre de jours annonce, ils revenaient a 80 a 100 euros
     * par jour : le tarif d'un integrateur de theme, pas celui d'un
     * developpeur Symfony avec douze ans de pratique. Un prix trop bas ne
     * remplit pas le carnet, il attire les projets qui negocient et fait fuir
     * ceux qui ont un budget.
     *
     * Tout se calcule donc a partir d'un seul nombre, modifiable dans les
     * paramètres : change ton taux, tout le site suit.
     *
     * 350 est le palier de depart conseille, pas une cible. Le passage de 250
     * a 350 se fait sur les nouveaux prospects d'abord : ils n'ont aucun prix
     * en tete, alors qu'un client existant qui voit son tarif bondir de 40 %
     * du jour au lendemain le vit comme une rupture de parole.
     */
    public const TJM_DEFAUT = 350;

    private Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /** Le taux journalier retenu, jamais absurde. */
    public function tjm(): int
    {
        $valeur = (int) $this->settings->get('tjm', (string) self::TJM_DEFAUT);

        return $valeur >= 100 ? $valeur : self::TJM_DEFAUT;
    }

    /**
     * Le prix d'une entree : soit un forfait pose, soit des jours de travail.
     *
     * @param array<string, mixed> $entree
     */
    public function prix(array $entree): int
    {
        if (isset($entree['forfait'])) {
            return (int) $entree['forfait'];
        }

        // Arrondi a la cinquantaine : un prix public affiche a l'euro pres
        // donne une precision que cette estimation n'a pas.
        return (int) (round((int) $entree['jours'] * $this->tjm() / 50) * 50);
    }

    /**
     * Les points de depart, prix calcules.
     *
     * @return array<int, array<string, mixed>>
     */
    public function basesChiffrees(): array
    {
        return array_map(function (array $b): array {
            $b['prix'] = $this->prix($b);

            return $b;
        }, self::bases());
    }

    /**
     * Les options, prix calcules.
     *
     * @return array<int, array<string, mixed>>
     */
    public function optionsChiffrees(): array
    {
        return array_map(function (array $o): array {
            $o['prix'] = $this->prix($o);

            return $o;
        }, self::options());
    }

    /**
     * Les points de depart. Le type technique correspond au formulaire de
     * devis, ce qui permet de pre-remplir sans que le visiteur ressaisisse.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function bases(): array
    {
        return [
            [
                'cle' => 'vitrine',
                'nom' => 'Site vitrine',
                'pour' => 'Présenter votre activité, être trouvé, être contacté',
                'jours' => 7,
                'type' => 'site_vitrine',
            ],
            [
                'cle' => 'boutique',
                'nom' => 'Boutique en ligne',
                'pour' => 'Vendre vos produits sans commission de plateforme',
                'jours' => 18,
                'type' => 'site_e-commerce',
            ],
            [
                'cle' => 'metier',
                'nom' => 'Application métier',
                'pour' => 'Remplacer les fichiers Excel et les échanges par e-mail',
                'jours' => 29,
                'type' => 'intranet',
            ],
            [
                'cle' => 'mobile',
                'nom' => 'Application web et mobile',
                'pour' => 'Une application que vos clients installent sur leur téléphone',
                'jours' => 43,
                'type' => 'application_cross_plateforme',
            ],
        ];
    }

    /**
     * Les options. « base » restreint l'option a certains points de depart :
     * proposer un paiement en ligne sur un site vitrine n'a pas de sens, et
     * une liste ou tout est possible n'aide personne a choisir.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function options(): array
    {
        return [
            [
                'cle' => 'pages',
                'nom' => 'Pages supplémentaires',
                'detail' => 'Par tranche de cinq pages au-delà des six incluses',
                'jours' => 3, 'bases' => ['vitrine', 'boutique'],
            ],
            [
                'cle' => 'blog',
                'nom' => 'Blog ou actualités',
                'detail' => "Vous publiez vous-même, sans appeler personne",
                'jours' => 4, 'bases' => ['vitrine', 'boutique', 'metier', 'mobile'],
            ],
            [
                'cle' => 'langues',
                'nom' => 'Deuxième langue',
                'detail' => 'Interface et contenus, avec bascule automatique',
                'jours' => 6, 'bases' => ['vitrine', 'boutique', 'metier', 'mobile'],
            ],
            [
                'cle' => 'paiement',
                'nom' => 'Paiement en ligne',
                'detail' => 'Carte bancaire via Stripe, reçus automatiques',
                'jours' => 7, 'bases' => ['boutique', 'metier', 'mobile'],
            ],
            [
                'cle' => 'comptes',
                'nom' => 'Espace client',
                'detail' => 'Vos clients consultent seuls leurs documents et leurs commandes',
                'jours' => 10, 'bases' => ['vitrine', 'boutique', 'metier', 'mobile'],
            ],
            [
                'cle' => 'rdv',
                'nom' => 'Prise de rendez-vous',
                'detail' => 'Créneaux, confirmation et rappel automatiques',
                'jours' => 7, 'bases' => ['vitrine', 'metier', 'mobile'],
            ],
            [
                'cle' => 'facturation',
                'nom' => 'Devis et factures',
                'detail' => 'Génération PDF, numérotation continue, suivi des règlements',
                'jours' => 12, 'bases' => ['metier', 'mobile'],
            ],
            [
                'cle' => 'seo',
                'nom' => 'Référencement de départ',
                'detail' => 'Structure, balises, sitemap et vitesse travaillés dès la mise en ligne',
                'jours' => 5, 'bases' => ['vitrine', 'boutique', 'metier', 'mobile'],
            ],
            [
                'cle' => 'contenus',
                'nom' => 'Reprise de vos contenus',
                'detail' => "Je récupère textes et images de l'ancien site, vous n'avez rien à ressaisir",
                'jours' => 4, 'bases' => ['vitrine', 'boutique', 'metier', 'mobile'],
            ],
            [
                'cle' => 'charte',
                'nom' => 'Identité graphique',
                'detail' => 'Logo, couleurs et typographies créés pour vous',
                'jours' => 8, 'bases' => ['vitrine', 'boutique', 'metier', 'mobile'],
            ],
            [
                'cle' => 'heberg',
                'nom' => 'Hébergement et supervision, 1 an',
                'detail' => 'Serveur, sauvegardes quotidiennes et surveillance incluse',
                'forfait' => 360, 'jours' => 0, 'bases' => ['vitrine', 'boutique', 'metier', 'mobile'],
            ],
            [
                'cle' => 'formation',
                'nom' => 'Formation',
                'detail' => 'Deux heures pour être autonome sur votre administration',
                'forfait' => 300, 'jours' => 0, 'bases' => ['vitrine', 'boutique', 'metier', 'mobile'],
            ],
        ];
    }

    /** Le type technique correspondant a une base, pour le formulaire de devis. */
    public static function typePourBase(string $cle): string
    {
        foreach (self::bases() as $base) {
            if ($base['cle'] === $cle) {
                return (string) $base['type'];
            }
        }

        return 'autre';
    }

    /** Le libelle d'une base ou d'une option, pour le resume envoye au devis. */
    public static function libelle(string $cle): ?string
    {
        foreach (array_merge(self::bases(), self::options()) as $e) {
            if ($e['cle'] === $cle) {
                return (string) $e['nom'];
            }
        }

        return null;
    }
}
