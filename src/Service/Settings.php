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
        'pterodactyl_api_url' => [
            'label' => "URL de l'API du panneau d'hébergement",
            'help' => "L'adresse de l'API Pterodactyl, telle que l'application la concatène avec l'e-mail du client. Exemple : https://panel.mondomaine.fr/api/resources?email=",
            'env' => 'API_RESOURCE_URL',
            'secret' => false,
        ],
        'pterodactyl_api_token' => [
            'label' => "Jeton de l'API du panneau d'hébergement",
            'help' => "Le jeton d'application Pterodactyl. Il n'est jamais affiché en clair une fois enregistré.",
            'env' => 'API_RESOURCE_TOKEN',
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
