<?php

namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=TicketRepository::class)
 */
class Ticket
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

    /**
     * @ORM\ManyToOne(targetEntity=Devis::class, inversedBy="tickets")
     */
    private $devis;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sentMessages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="receivedMessages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $receiver;

    /**
    * @ORM\OneToMany(targetEntity=Message::class, mappedBy="ticket", cascade={"persist", "remove"})
    */
    private Collection $messages;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="ticket", cascade={"persist", "remove"})
     */
    private Collection $attachments;

    /**
     * @throws \Exception
     */
    public function __construct(){
        $this->messages = new ArrayCollection();
        $this->createdAt = new \Datetime('now', new \DateTimeZone('Europe/Paris'));
        $this->updatedAt = new \Datetime('now', new \DateTimeZone('Europe/Paris'));
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
    public function getReceiver():?User
    {
        return $this->receiver;
    }

    /**
     * @param mixed $receiver
     * @return Ticket
     */
    public function setReceiver($receiver):self
    {
        $this->receiver = $receiver;
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
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Ticket
     */
    public function setCreatedAt(\DateTime $createdAt): Ticket
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt(): ?\DateTime
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

    /**
     * @return mixed
     */
    public function getSender():?User
    {
        return $this->sender;
    }

    /**
     * @param mixed $sender
     * @return Ticket
     */
    public function setSender($sender):self
    {
        $this->sender = $sender;
        return $this;
    }
    public function getMessages(): Collection {
        return $this->messages;
    }
    
    public function addMessage(Message $message): self {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setTicket($this);
        }
        return $this;
    }
    
    public function removeMessage(Message $message): self {
        if ($this->messages->removeElement($message)) {
            if ($message->getTicket() === $this) {
                $message->setTicket(null);
            }
        }
        return $this;
    }

    public function getAttachments(): Collection {
        return $this->attachments;
    }

    public function addAttachment(Image $attachment): self {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
            $attachment->setTickets($this);
        }
        return $this;
    }

    public function removeAttachment(Image $attachment): self {
        if ($this->attachments->removeElement($attachment)) {
            if ($attachment->getTickets() === $this) {
                $attachment->setTickets(null);
            }
        }
        return $this;
    }
}