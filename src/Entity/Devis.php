<?php

namespace App\Entity;


use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DevisRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Uuid;

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
    private  ?Uuid $id;

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
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="devis_id")
     * @ORM\JoinColumn(nullable=false)
     */
    private $devisUser;

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

    public function getDevisUser(): ?User
    {
        return $this->devisUser;
    }

    public function setDevisUser(?User $devisUser): self
    {
        $this->devisUser = $devisUser;

        return $this;
    }
}
