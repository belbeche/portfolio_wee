<?php

namespace App\Entity;

use App\Repository\DevisRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DevisRepository::class)
 */
class Devis
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type_de_site_web;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $attentes_design_web;

    /**
     * @ORM\Column(type="text")
     */
    private $description_projet;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $multi_langues;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="devis")
     */
    private $DevisUser;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    public function __construct()
    {
        $this->DevisUser = new ArrayCollection();
        $this->created_at = new DateTimeImmutable('now');
    }

    public function getId(): ?int
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
     * @return Collection<int, User>
     */
    public function getDevisUser(): Collection
    {
        return $this->DevisUser;
    }

    public function addDevisUser(User $devisUser): self
    {
        if (!$this->DevisUser->contains($devisUser)) {
            $this->DevisUser[] = $devisUser;
            $devisUser->setDevis($this);
        }

        return $this;
    }

    public function removeDevisUser(User $devisUser): self
    {
        if ($this->DevisUser->removeElement($devisUser)) {
            // set the owning side to null (unless already changed)
            if ($devisUser->getDevis() === $this) {
                $devisUser->setDevis(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
