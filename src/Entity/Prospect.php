<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\ProspectionTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProspectRepository")
 */
class Prospect
{
    use ProspectionTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="doctrine.uuid_generator")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $services = [];

    /**
     * @ORM\Column(type="text", nullable=true) 
     */
    private $documentFilenames; 

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getServices(): array
    {
        return $this->services;
    }

    public function setServices(array $services): self
    {
        $this->services = $services;
        return $this;
    }

    public function getDocumentFilenames(): ?string
    {
        return $this->documentFilenames;
    }

    public function setDocumentFilenames(?string $filenames): self
    {
        $this->documentFilenames = $filenames; 
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Journal des echanges. Le champ notes historique reste en place :
     * il sert de resume libre, tandis que ces entrees conservent la
     * trace datee de chaque contact.
     *
     * @ORM\OneToMany(targetEntity=ProspectNote::class, mappedBy="prospect", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private Collection $prospectNotes;

    public function __construct()
    {
        $this->prospectNotes = new ArrayCollection();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
    }

    /** @return Collection<int, ProspectNote> */
    public function getProspectNotes(): Collection
    {
        return $this->prospectNotes;
    }

    public function addProspectNote(ProspectNote $note): self
    {
        if (!$this->prospectNotes->contains($note)) {
            $this->prospectNotes[] = $note;
            $note->setProspect($this);
        }

        return $this;
    }

    public function removeProspectNote(ProspectNote $note): self
    {
        if ($this->prospectNotes->removeElement($note) && $note->getProspect() === $this) {
            $note->setProspect(null);
        }

        return $this;
    }
}