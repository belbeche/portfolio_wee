<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CallBackRequestRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CallBackRequestRepository")
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
     * @Assert\NotBlank(message="Le nom est obligatoire.")
     * @Assert\Length(
     *     max=50,
     *     maxMessage="Le nom ne peut pas dépasser {{ limit }} caractères."
     * )
     * @ORM\Column(type="string", length=50)
     */
    private ?string $name = null;

    /**
    * @Assert\NotBlank(message="L'indicatif téléphonique est obligatoire.")
    * @ORM\Column(type="string", length=5)
    */
    private ?string $phonePrefix = '+33';

    /**
    * @Assert\NotBlank(message="Le numéro de téléphone est obligatoire.")
    * @Assert\Regex(
    *     pattern="/^[1-9](\d{2}){4}$/",
    *     message="Le numéro de téléphone doit être valide pour l'indicatif sélectionné."
    * )
    *   @ORM\Column(type="string", length=20)
    */
    private ?string $phone = null;

    /**
     * @Assert\NotBlank(message="L'email est obligatoire.")
     * @Assert\Email(message="L'email doit être valide.")
     * @ORM\Column(type="string", length=100)
     */
    private ?string $email = null;

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

    public function getPhonePrefix(): ?string
    {
        return $this->phonePrefix;
    }

    public function setPhonePrefix(?string $phonePrefix): self
    {
        $this->phonePrefix = $phonePrefix;
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