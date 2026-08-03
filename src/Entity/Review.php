<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Avis client.
 *
 * Publie uniquement apres moderation : approved reste a false tant que
 * l'administrateur ne l'a pas valide depuis /admin/avis.
 *
 * @ORM\Entity(repositoryClass=ReviewRepository::class)
 * @ORM\Table(name="review")
 */
class Review
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120)
     * @Assert\NotBlank(message="Votre nom est necessaire pour publier l'avis.")
     * @Assert\Length(max=120)
     */
    private ?string $authorName = null;

    /**
     * @ORM\Column(type="string", length=160, nullable=true)
     * @Assert\Length(max=160)
     */
    private ?string $company = null;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Range(min=1, max=5)
     */
    private int $rating = 5;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Quelques mots suffisent, mais il en faut.")
     * @Assert\Length(min=10, max=1200)
     */
    private ?string $content = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $approved = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getAuthorName(): ?string { return $this->authorName; }
    public function setAuthorName(?string $v): self { $this->authorName = $v; return $this; }

    public function getCompany(): ?string { return $this->company; }
    public function setCompany(?string $v): self { $this->company = $v; return $this; }

    public function getRating(): int { return $this->rating; }
    public function setRating(int $v): self { $this->rating = max(1, min(5, $v)); return $this; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $v): self { $this->content = $v; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function isApproved(): bool { return $this->approved; }
    public function setApproved(bool $v): self { $this->approved = $v; return $this; }
}
