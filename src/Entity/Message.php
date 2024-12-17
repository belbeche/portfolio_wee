<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Monolog\DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity()
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="doctrine.uuid_generator")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTime $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $priority;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $attachment;

    /**
     * @ORM\ManyToOne(targetEntity=Devis::class, inversedBy="messages", cascade={"remove"})
     */
    private $devis;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $sender;

     /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="receivedMessages")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $receiver = null;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param mixed $attachment
     * @return Message
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
        return $this;
    }

    public function getDevis(): ?Devis
    {
        return $this->devis;
    }

    public function setDevis(?Devis $devis): self
    {
        $this->devis = $devis;
        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get the value of receiver
     *
     * @return ?User
     */
    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    /**
     * Set the value of receiver
     *
     * @param ?User $receiver
     *
     * @return self
     */
    public function setReceiver(?User $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }
}