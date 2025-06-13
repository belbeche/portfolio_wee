<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ImageRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image
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
    private ?string $name;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="images")
     * @ORM\JoinColumn(name="`project_id`")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity=Subject::class, inversedBy="images")
     */
    private ?Subject $subject = null;

    /**
     * @ORM\ManyToMany(targetEntity=Ticket::class, mappedBy="attachments")
     */
    private ?Collection $tickets = null;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function __toString()
    {
        return $this->name ?? '';
    }
    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $Subject): self
    {
        $this->subject = $Subject;

        return $this;
    }

    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getAttachments() === $this) {
                $ticket->getAttachments(null);
            }
        }

        return $this;
    }


    /**
     * Set the value of tickets
     */
    public function setTickets($tickets): self
    {
        $this->tickets = $tickets;

        return $this;
    }
}
