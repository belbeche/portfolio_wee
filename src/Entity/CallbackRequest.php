<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class CallbackRequest
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Le nom et prénom est obligatoire.")
     * @Assert\Length(min=2, max=100, minMessage="Le nom et prénom doit contenir au moins 2 caractères.")
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @Assert\NotBlank(message="Le numéro de téléphone est obligatoire.")
     * @Assert\Regex(pattern="/^\+?[0-9]*$/", message="Le numéro de téléphone n'est pas valide.")
     * @ORM\Column(type="string", length=20)
     */
    private $phone;

    /**
     * @Assert\NotBlank(message="L'email est obligatoire.")
     * @Assert\Email(message="L'email n'est pas valide.")
     * @ORM\Column(type="string", length=100)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, options={"default": "pending"})
     */
    private $status = 'pending'; // Statut: pending, validated, canceled, or relaunched

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $relaunchDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getRelaunchDate(): ?\DateTimeInterface
    {
        return $this->relaunchDate;
    }

    public function setRelaunchDate(?\DateTimeInterface $relaunchDate): self
    {
        $this->relaunchDate = $relaunchDate;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }
}