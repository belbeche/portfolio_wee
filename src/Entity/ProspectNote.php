<?php

namespace App\Entity;

use App\Repository\ProspectNoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Un echange avec un prospect.
 *
 * L'entite Prospect avait un champ notes unique, ecrase a chaque
 * modification. On perdait donc l'historique, alors que c'est precisement
 * ce qui sert : savoir ce qui a ete dit au dernier appel avant de rappeler.
 *
 * Chaque note porte son type, ce qui permet de distinguer un compte rendu
 * d'appel d'un simple pense-bete, et de programmer la relance suivante.
 *
 * @ORM\Entity(repositoryClass=ProspectNoteRepository::class)
 * @ORM\Table(name="prospect_note")
 */
class ProspectNote
{
    public const TYPES = [
        'note' => 'Note',
        'appel' => 'Appel',
        'email' => 'E-mail',
        'rdv' => 'Rendez-vous',
        'proposition' => 'Proposition',
        'refus' => 'Refus',
    ];

    /**
     * Type pose par le systeme, jamais proposé dans le formulaire : un envoi
     * qui a echoue. Il garde la trace de l'erreur exacte du serveur d'envoi
     * sans jamais compter comme un contact, donc le prospect reste candidat.
     */
    public const TYPE_EMAIL_ECHEC = 'email-echec';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Prospect::class, inversedBy="prospectNotes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?Prospect $prospect = null;

    /**
     * @ORM\Column(type="string", length=30, options={"default": "note"})
     */
    private string $type = 'note';

    /**
     * @ORM\Column(type="text")
     */
    private ?string $content = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Nom de la personne qui a saisi la note.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $author = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
    }

    public function getId(): ?int { return $this->id; }

    public function getProspect(): ?Prospect { return $this->prospect; }
    public function setProspect(?Prospect $prospect): self { $this->prospect = $prospect; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function getTypeLabel(): string
    {
        if (self::TYPE_EMAIL_ECHEC === $this->type) {
            return 'E-mail refuse';
        }

        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getAuthor(): ?string { return $this->author; }
    public function setAuthor(?string $author): self { $this->author = $author; return $this; }

    /** Couleur d'affichage associee au type. */
    public function getTone(): string
    {
        return [
            'appel' => 'blue',
            'rdv' => 'green',
            'proposition' => 'violet',
            'refus' => 'red',
            'email' => 'gold',
            self::TYPE_EMAIL_ECHEC => 'red',
        ][$this->type] ?? 'muted';
    }
}
