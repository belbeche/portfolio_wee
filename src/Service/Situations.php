<?php

namespace App\Service;

/**
 * Le catalogue technique, retourne en situations.
 *
 * Le probleme du formulaire precedent : il demandait a un visiteur de choisir
 * entre « Systeme de Gestion de Contenu (CMS) » et « Intranet d'entreprise ».
 * Ces mots ne veulent rien dire pour quelqu'un qui n'est pas du metier. Face a
 * une liste qu'il ne comprend pas, il choisit au hasard, ou il ferme la page.
 *
 * On lui montre donc des phrases dans lesquelles il peut se reconnaitre, et
 * c'est nous qui traduisons en categorie technique. Il ne choisit plus un
 * produit, il decrit sa situation.
 *
 * Chaque entree contient :
 *   phrase        ce que la personne se dit a elle-meme, a la premiere personne
 *   consequence   ce que ca lui coute aujourd'hui, concretement
 *   reponse       ce que je construis pour elle
 *   type          la valeur technique envoyee au formulaire
 */
class Situations
{
    /** @return array<int, array<string, string>> */
    public static function toutes(): array
    {
        return [
            [
                'cle' => 'refus',
                'phrase' => "Je refuse des projets faute de développeur.",
                'consequence' => "Vous laissez passer du chiffre d'affaires que vous auriez pu prendre, et le client va chez un concurrent.",
                'reponse' => "Je développe en marque blanche, sous votre nom. Vos clients ne me voient jamais.",
                'type' => 'application_cross_plateforme',
                'pour' => 'Agences, studios, indépendants',
            ],
            [
                'cle' => 'vieux',
                'phrase' => "Mon site date d'il y a cinq ans et je n'ose plus l'envoyer.",
                'consequence' => "Un prospect qui le voit avant de vous rencontrer se fait un avis avant même de vous parler.",
                'reponse' => "Une refonte qui garde ce qui marche, et qui se met à jour toute seule ensuite.",
                'type' => 'site_vitrine',
                'pour' => 'Artisans, PME, professions libérales',
            ],
            [
                'cle' => 'vendre',
                'phrase' => "Je veux vendre en ligne sans dépendre d'une plateforme.",
                'consequence' => "Sur une place de marché, vous payez une commission et vous ne possédez pas vos clients.",
                'reponse' => "Une boutique à vous, avec paiement, stock et factures. Vos données restent les vôtres.",
                'type' => 'site_e-commerce',
                'pour' => 'Commerçants, producteurs, créateurs',
            ],
            [
                'cle' => 'excel',
                'phrase' => "Mon activité tourne sur des fichiers Excel qui se marchent dessus.",
                'consequence' => "Deux personnes modifient la même ligne, personne ne sait quelle version fait foi.",
                'reponse' => "Un outil sur mesure où chacun voit la même information à la seconde près.",
                'type' => 'intranet',
                'pour' => 'Équipes de 3 à 50 personnes',
            ],
            [
                'cle' => 'telephone',
                'phrase' => "Je passe mes journées à répondre au téléphone pour des questions répétitives.",
                'consequence' => "Chaque appel coûte dix minutes que vous ne passez pas à produire.",
                'reponse' => "Un espace client où ils trouvent seuls leurs devis, leurs documents et l'état de leur commande.",
                'type' => 'application_cross_plateforme',
                'pour' => 'Prestataires de services',
            ],
            [
                'cle' => 'mobile',
                'phrase' => "Il me faut une application que mes clients installent sur leur téléphone.",
                'consequence' => "Un site ne se range pas sur un écran d'accueil, et n'envoie pas de notification.",
                'reponse' => "Une application qui fonctionne sur le web, iPhone et Android à partir d'un seul code.",
                'type' => 'application_cross_plateforme',
                'pour' => 'Services du quotidien, communautés',
            ],
            [
                'cle' => 'invisible',
                'phrase' => "Personne ne me trouve sur Google.",
                'consequence' => "Vos concurrents captent les recherches de vos futurs clients à votre place.",
                'reponse' => "Un site pensé pour être lu par les moteurs, et du contenu qui répond aux vraies questions.",
                'type' => 'site_blog',
                'pour' => 'Toute activité locale',
            ],
            [
                'cle' => 'montrer',
                'phrase' => "Mon travail est visuel et je n'ai nulle part où le montrer.",
                'consequence' => "Envoyer un dossier PDF de trente mégaoctets par mail ne donne envie à personne.",
                'reponse' => "Un portfolio rapide, où vos images sont mises en valeur et se partagent par un lien.",
                'type' => 'site_portfolio',
                'pour' => 'Photographes, architectes, artisans d\'art',
            ],
            [
                'cle' => 'biens',
                'phrase' => "Je gère des biens ou des annonces et je les mets à jour à la main partout.",
                'consequence' => "Une annonce vendue reste en ligne trois jours, et vous recevez des appels pour rien.",
                'reponse' => "Un catalogue d'annonces avec recherche, filtres et mise à jour en un seul endroit.",
                'type' => 'site_real_estate',
                'pour' => 'Agences immobilières, concessions, locations',
            ],
            [
                'cle' => 'communaute',
                'phrase' => "J'ai une communauté et elle est éparpillée sur des groupes Facebook.",
                'consequence' => "Vous construisez une audience sur un terrain qui ne vous appartient pas.",
                'reponse' => "Un espace d'échange à vous, avec comptes, sujets et modération.",
                'type' => 'site_forum',
                'pour' => 'Associations, clubs, réseaux professionnels',
            ],
            [
                'cle' => 'publier',
                'phrase' => "Je publie souvent et je dois appeler quelqu'un à chaque fois.",
                'consequence' => "Vous payez, vous attendez, et vous finissez par ne plus publier du tout.",
                'reponse' => "Une administration simple où vous écrivez et publiez vous-même, sans intermédiaire.",
                'type' => 'systeme_gestion_contenu',
                'pour' => 'Médias, institutions, écoles',
            ],
            [
                'cle' => 'autre',
                'phrase' => "Mon besoin ne ressemble à rien de tout ça.",
                'consequence' => "Les solutions toutes faites vous obligent à tordre votre façon de travailler.",
                'reponse' => "On en parle vingt minutes, et je vous dis honnêtement si c'est faisable et à quel prix.",
                'type' => 'autre',
                'pour' => 'Besoins spécifiques',
            ],
        ];
    }

    /**
     * Les choix de design, traduits eux aussi.
     *
     * La cle est le libelle enregistre en base : on n'y touche pas, on ajoute
     * seulement la phrase qui le rend comprehensible. Un libelle inconnu
     * s'affiche simplement sans explication, sans rien casser.
     *
     * @return array<string, string>
     */
    public static function aidesDesign(): array
    {
        return [
            'Design standard' => "Vous voulez que ce soit propre et efficace, sans y passer trois semaines.",
            'Avec vos propres maquettes' => "Vous avez déjà des maquettes ou une charte : je les respecte à la lettre.",
            'Design avancé' => "Vous voulez que le site se démarque vraiment de ceux de vos concurrents.",
            'Design orienté vers le mobile' => "Vos clients arrivent presque tous depuis leur téléphone.",
            'Design animé ou interactif' => "Vous voulez que la page réagisse, bouge, retienne l'attention.",
            'Thème sombre' => "Vos utilisateurs passent des heures sur l'écran, souvent le soir.",
            'Thème clair' => "Vous voulez quelque chose de lumineux, lisible en plein jour.",
            'Design axé sur l\'accessibilité' => "Vos clients ont parfois plus de 60 ans, ou utilisent un lecteur d'écran.",
            'Design minimaliste' => "Vous voulez qu'on aille droit au but, sans distraction.",
            'Branding complet inclus' => "Vous partez de zéro : il vous faut aussi un logo et des couleurs.",
            'Personnalisation avancée' => "Vous voulez pouvoir tout régler vous-même, dans le détail.",
        ];
    }

    /** L'aide correspondant a un libelle, ou null si on ne le connait pas. */
    public static function aideDesign(string $libelle): ?string
    {
        $aides = self::aidesDesign();

        foreach ($aides as $cle => $aide) {
            // Comparaison souple : les libelles en base ont parfois une
            // majuscule ou un accent de difference.
            if (0 === strcasecmp(trim($cle), trim($libelle))) {
                return $aide;
            }
        }

        return null;
    }
}
