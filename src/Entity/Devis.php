<?php

namespace App\Entity;


use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DevisRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=DevisRepository::class)
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $multi_langues;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="devis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->created_at = new DateTimeImmutable('now');
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

    public function getMultiLangues(): ?string
    {
        return $this->multi_langues;
    }

    public function setMultiLangues(string $multi_langues): self
    {
        $this->multi_langues = $multi_langues;

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
}
