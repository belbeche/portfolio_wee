<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Une competence, administrable depuis le back-office.
 *
 * Elles etaient jusqu'ici codees en dur dans le gabarit de l'accueil, sous
 * forme d'un tableau Twig de noms et d'images. Impossible d'en ajouter une
 * sans toucher au code, donc impossible de faire evoluer la vitrine au
 * rythme reel des competences.
 *
 * @ORM\Entity(repositoryClass=SkillRepository::class)
 * @ORM\Table(name="skill")
 */
class Skill
{
    /**
     * Le niveau n'est pas une note sur 5, c'est un engagement.
     * Chaque palier dit au client ce qu'il peut me confier, pas ce que je vaux.
     */
    public const LEVELS = [
        5 => ['label' => "Coeur de metier",    'hint' => "Je m'en sers tous les jours depuis des annees. C'est la que je suis le meilleur."],
        4 => ['label' => "En toute autonomie", 'hint' => "Je peux construire et mettre en ligne votre projet avec, seul, de bout en bout."],
        3 => ['label' => "A l'aise",           'hint' => "Je peux reprendre et faire evoluer un projet existant qui l'utilise."],
        2 => ['label' => "Operationnel",       'hint' => "Je m'en sers quand un projet le demande, avec un temps de prise en main."],
        1 => ['label' => "Notions",            'hint' => "Je comprends et je peux dialoguer avec un specialiste, je ne le porte pas seul."],
    ];

    public const GROUPS = [
        'mobile' => 'Mobile',
        'backend' => 'Back-end et API',
        'frontend' => 'Front-end',
        'infra' => 'Infrastructure et outillage',
        'method' => 'Methode et gestion de projet',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $skillGroup = 'backend';

    /**
     * Niveau de maitrise, de 1 a 5. Sert a calculer la barre affichee.
     *
     * @ORM\Column(type="integer", options={"default": 3})
     */
    private int $level = 3;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $yearsOfExperience = null;

    /**
     * Nom du fichier dans public/img/clients, facultatif.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $icon = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $visible = true;

    /**
     * Les competences mises en avant remontent sur l'accueil.
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $highlighted = false;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $position = 0;

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getSkillGroup(): string { return $this->skillGroup; }
    public function setSkillGroup(string $skillGroup): self { $this->skillGroup = $skillGroup; return $this; }
    public function getGroupLabel(): string { return self::GROUPS[$this->skillGroup] ?? $this->skillGroup; }

    public function getLevel(): int { return $this->level; }
    public function setLevel(int $level): self { $this->level = max(1, min(5, $level)); return $this; }

    /** Pourcentage pour la barre de progression. */
    public function getLevelPercent(): int { return (int) round($this->level / 5 * 100); }

    /** Ce que le niveau signifie, en francais courant. */
    public function getLevelLabel(): string { return self::LEVELS[$this->level]['label'] ?? ''; }

    /** La phrase qui explique le palier a quelqu'un qui n'est pas developpeur. */
    public function getLevelHint(): string { return self::LEVELS[$this->level]['hint'] ?? ''; }

    /** Choix pretes a l'emploi pour le formulaire d'administration. */
    public static function levelChoices(): array
    {
        $out = [];
        foreach (self::LEVELS as $n => $d) { $out[$n.' - '.$d['label']] = $n; }
        return $out;
    }

    public function getYearsOfExperience(): ?int { return $this->yearsOfExperience; }
    public function setYearsOfExperience(?int $years): self { $this->yearsOfExperience = $years; return $this; }

    public function getIcon(): ?string { return $this->icon; }
    public function setIcon(?string $icon): self { $this->icon = $icon; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function isVisible(): bool { return $this->visible; }
    public function setVisible(bool $visible): self { $this->visible = $visible; return $this; }

    public function isHighlighted(): bool { return $this->highlighted; }
    public function setHighlighted(bool $highlighted): self { $this->highlighted = $highlighted; return $this; }

    public function getPosition(): int { return $this->position; }
    /**
     * Accepte null et le ramene a zero.
     *
     * Le champ « ordre d'affichage » est facultatif dans le formulaire, et
     * un champ facultatif laisse passer null. La signature refusait ce null
     * et la page tombait en erreur 500 au moment d'enregistrer. Une position
     * absente veut dire « en premier », pas « plantage ».
     */
    public function setPosition(?int $position): self { $this->position = $position ?? 0; return $this; }

    public function __toString(): string { return (string) $this->name; }
}
