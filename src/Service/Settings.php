<?php

namespace App\Service;

use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Acces central aux reglages de l'application.
 *
 * Ordre de resolution d'une valeur :
 *   1. la base de donnees (modifiable depuis /admin/parametres)
 *   2. la variable d'environnement de repli, si declaree
 *   3. la valeur par defaut du catalogue
 *
 * Ajouter un reglage = ajouter une entree dans CATALOG. Il apparait
 * automatiquement dans la page d'administration au deploiement
 * suivant, sans migration ni script : la ligne en base n'est creee
 * qu'a la premiere sauvegarde.
 */
class Settings
{
    /**
     * Le catalogue des reglages connus.
     *  label  : nom affiche dans l'administration
     *  help   : explication en francais courant
     *  env    : variable d'environnement de repli (facultatif)
     *  secret : true = valeur masquee a l'affichage
     */
    public const CATALOG = [
        'asset_version' => [
            'label' => 'Version des fichiers statiques (cache)',
            'help' => "Ajoutée aux adresses des CSS et scripts. Le bouton « Forcer l'actualisation » l'incrémente : tous les visiteurs rechargent alors les derniers fichiers, sans vider leur cache à la main.",
            'env' => null,
            'secret' => false,
        ],
        'pterodactyl_panel_url' => [
            'label' => "URL du panneau Pterodactyl",
            'help' => "L'adresse de base du panneau, sans /api ni barre finale. Exemple : https://panel.mondomaine.fr",
            'env' => null,
            'secret' => false,
        ],
        'pterodactyl_client_key' => [
            'label' => "Clé d'API client Pterodactyl",
            'help' => "Une clé personnelle commençant par ptlc_, à créer dans le panneau sur Paramètres du compte puis Identifiants API (/account/api). Jamais affichée en clair une fois enregistrée.",
            'env' => null,
            'secret' => true,
        ],
        'pterodactyl_api_url' => [
            'label' => "Ancien relais d'hébergement : URL (repli)",
            'help' => "Utilisé seulement si le panneau officiel ci-dessus n'est pas renseigné. Exemple : https://panel.mondomaine.fr/api/resources?email=",
            'env' => 'API_RESOURCE_URL',
            'secret' => false,
        ],
        'pterodactyl_api_token' => [
            'label' => "Ancien relais d'hébergement : jeton (repli)",
            'help' => "Le jeton d'application Pterodactyl. Il n'est jamais affiché en clair une fois enregistré.",
            'env' => 'API_RESOURCE_TOKEN',
            'secret' => true,
        ],
        'vapid_public_key' => [
            'label' => 'Notifications push : clé publique VAPID',
            'help' => "Générée automatiquement au premier abonnement. Ne pas modifier à la main.",
            'env' => null,
            'secret' => false,
        ],
        'vapid_private_key' => [
            'label' => 'Notifications push : clé privée VAPID',
            'help' => "Générée automatiquement, jamais affichée. La vider force une nouvelle paire (les abonnés devront se réabonner).",
            'env' => null,
            'secret' => true,
        ],
        'axishumain_url' => [
            'label' => "URL de la plateforme AxisHumain",
            'help' => "L'adresse de base, sans barre finale. Exemple : https://axishumain.fr",
            'env' => 'AXISHUMAIN_URL',
            'secret' => false,
        ],
        'axishumain_client_path' => [
            'label' => "Chemin d'inscription client sur AxisHumain",
            'help' => "Ajouté à l'URL de base pour le bouton « Publier une mission ». Exemple : /inscription?type=client",
            'env' => null,
            'secret' => false,
        ],
        'axishumain_expert_path' => [
            'label' => "Chemin d'inscription expert sur AxisHumain",
            'help' => "Ajouté à l'URL de base pour le bouton « Créer mon profil expert ». Exemple : /inscription?type=expert",
            'env' => null,
            'secret' => false,
        ],
        'calendly_url' => [
            'label' => 'Lien de réservation d\'appel',
            'help' => 'Le lien Calendly proposé aux prospects et clients.',
            'env' => null,
            'secret' => false,
        ],
        'entreprise_nom' => [
            'label' => 'Devis : raison sociale',
            'help' => "Le nom qui figure en haut du devis et dans les mentions legales du bas de page.",
            'env' => null,
            'secret' => false,
        ],
        'entreprise_adresse' => [
            'label' => 'Devis : adresse du siege',
            'help' => "Adresse complete sur une seule ligne. Exemple : 12 rue des Lilas, 44000 Nantes. C'est une mention obligatoire sur un devis.",
            'env' => null,
            'secret' => false,
        ],
        'entreprise_siren' => [
            'label' => 'Devis : SIREN ou SIRET',
            'help' => "Mention obligatoire. Le SIRET est preferable au SIREN, il identifie l'etablissement.",
            'env' => null,
            'secret' => false,
        ],
        'entreprise_tva' => [
            'label' => 'Devis : numero de TVA intracommunautaire',
            'help' => "Laisse vide si tu es en franchise en base de TVA : le devis affichera alors la mention « TVA non applicable, article 293 B du CGI » et ne calculera aucune taxe.",
            'env' => null,
            'secret' => false,
        ],
        'entreprise_iban' => [
            'label' => 'Facture : IBAN de reglement',
            'help' => "Affiche sur les factures pour que le client puisse virer sans te le demander. Laisse vide pour ne rien afficher.",
            'env' => null,
            'secret' => false,
        ],
        'devis_tva_taux' => [
            'label' => 'Devis : taux de TVA en pourcentage',
            'help' => "20 par defaut. Mets 0 si tu es en franchise en base. Le devis calcule le total TTC a partir de ce taux.",
            'env' => null,
            'secret' => false,
        ],
        'devis_validite_jours' => [
            'label' => 'Devis : duree de validite en jours',
            'help' => "30 par defaut. La duree de validite de l'offre est une mention obligatoire sur un devis.",
            'env' => null,
            'secret' => false,
        ],
        'devis_penalites' => [
            'label' => 'Devis : taux des penalites de retard',
            'help' => "Exemple : 3 fois le taux d'interet legal. Exigible sans rappel prealable des le lendemain de l'echeance.",
            'env' => null,
            'secret' => false,
        ],
        'portail_accueil' => [
            'label' => "Portail d'entrée du site",
            'help' => "Le grand écran d'accueil qui oriente le visiteur à son arrivée. Mets « non » pour l'éteindre, « visite » pour le montrer une fois par visite, « toujours » pour le montrer à chaque chargement de l'accueil. Par défaut : visite.",
            'env' => null,
            'secret' => false,
        ],
        'contact_phone' => [
            'label' => 'Téléphone affiché sur le site',
            'help' => 'Utilisé par les pages de contact et l\'espace client.',
            'env' => null,
            'secret' => false,
        ],
    ];

    private EntityManagerInterface $em;

    /** @var array<string, string|null>|null */
    private ?array $loaded = null;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function get(string $name, ?string $default = null): ?string
    {
        $this->load();

        // 1. La base
        if (array_key_exists($name, $this->loaded) && null !== $this->loaded[$name] && '' !== $this->loaded[$name]) {
            return $this->loaded[$name];
        }

        // 2. La variable d'environnement de repli
        $envName = self::CATALOG[$name]['env'] ?? null;
        if (null !== $envName) {
            $envValue = $_ENV[$envName] ?? $_SERVER[$envName] ?? null;
            if (null !== $envValue && '' !== $envValue) {
                return $envValue;
            }
        }

        // 3. Le defaut
        return $default;
    }

    public function set(string $name, ?string $value): void
    {
        $repo = $this->em->getRepository(Setting::class);
        $setting = $repo->findOneBy(['name' => $name]);

        if (null === $setting) {
            $setting = new Setting($name, $value);
            $this->em->persist($setting);
        } else {
            $setting->setValue($value);
        }

        $this->em->flush();
        $this->loaded = null; // le prochain get relit la base
    }

    /** Valeur brute stockee en base, sans repli env (pour l'administration). */
    public function getStored(string $name): ?string
    {
        $this->load();

        return $this->loaded[$name] ?? null;
    }

    private function load(): void
    {
        if (null !== $this->loaded) {
            return;
        }

        $this->loaded = [];

        try {
            foreach ($this->em->getRepository(Setting::class)->findAll() as $setting) {
                $this->loaded[$setting->getName()] = $setting->getValue();
            }
        } catch (\Throwable $e) {
            // Table pas encore creee : les replis env et defauts prennent
            // le relais, l'application ne tombe pas.
        }
    }
}
