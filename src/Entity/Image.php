<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ImageRepository;

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
    private $Subject;

    /**
     * Une image appartient a un seul ticket ; c'est le ticket qui possede
     * plusieurs pieces jointes, pas l'inverse. L'ancien mapping declarait
     * la relation dans le mauvais sens des deux cotes.
     *
     * @ORM\ManyToOne(targetEntity=Ticket::class, inversedBy="attachments")
     */
    private ?Ticket $ticket = null;

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
        return $this->Subject;
    }

    public function setSubject(?Subject $Subject): self
    {
        $this->Subject = $Subject;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }
}
