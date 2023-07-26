<?php

namespace App\Entity;


use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DevisRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DevisRepository", repositoryClass=DevisRepository::class)
 */
class Devis
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="doctrine.uuid_generator")
     */
    private  $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type_de_site_web;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $attentes_design_web;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description_projet;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="devis")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?User $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message="L'adresse email '{{ value }}' n'est pas valide.")
     * @ORM\Column(nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $statut;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="devis")
     */
    private $messages;

    public function __construct()
    {
        $this->created_at = new DateTimeImmutable('now');
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTypeDeSiteWeb(): ?string
    {
        return $this->type_de_site_web;
    }

    public function setTypeDeSiteWeb(string $type_de_site_web): self
    {
        $this->type_de_site_web = $type_de_site_web;

        return $this;
    }

    public function getAttentesDesignWeb(): ?string
    {
        return $this->attentes_design_web;
    }

    public function setAttentesDesignWeb(string $attentes_design_web): self
    {
        $this->attentes_design_web = $attentes_design_web;

        return $this;
    }

    public function getDescriptionProjet(): ?string
    {
        return $this->description_projet;
    }

    public function setDescriptionProjet(string $description_projet): self
    {
        $this->description_projet = $description_projet;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    /**
     * @param DateTimeImmutable $created_at
     * @return Devis
     */
    public function setCreatedAt(DateTimeImmutable $created_at): Devis
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }
    public function setEmailFromUser(?User $user): void
    {
        if ($user) {
            // Utilisateur connecté, utilisez son adresse email
            $this->email = $user->getEmail();
        }
    }

}
