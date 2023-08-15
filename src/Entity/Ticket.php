<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity()
 */
class Ticket extends \App\Entity\Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="doctrine.uuid_generator")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tickets")
     */
    private $sender;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $receiverEmail;

    /**
     * @ORM\ManyToOne(targetEntity=Devis::class)
     */
    private $devis;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $priority;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        $this->updatedAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return \App\Controller\Back\Ticket
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSender(): ?User
    {
        return $this->sender;
    }

    /**
     * @param mixed $sender
     * @return Ticket
     */
    public function setSender($sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReceiverEmail()
    {
        return $this->receiverEmail;
    }

    /**
     * @param mixed $receiverEmail
     * @return Ticket
     */
    public function setReceiverEmail($receiverEmail)
    {
        $this->receiverEmail = $receiverEmail;
        return $this;
    }

    public function getDevis(): ?Devis
    {
        return $this->devis;
    }

    /**
     * @param mixed $devis
     * @return Ticket
     */
    public function setDevis($devis): self
    {
        $this->devis = $devis;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     * @return Ticket
     */
    public function setPriority($priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Ticket
     */
    public function setStatus($status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return Ticket
     */
    public function setUpdatedAt($updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}