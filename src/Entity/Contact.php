<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private mixed $id;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private mixed $firstName;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private mixed $lastName;
    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private mixed $email;
    /**
     * @Assert\Positive
     */
    private mixed $phone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private mixed $service;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Contact
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName(): mixed
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * @return Contact
     */
    public function setFirstName($firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName(): mixed
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     * @return Contact
     */
    public function setLastName($lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail(): mixed
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return Contact
     */
    public function setEmail($email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone(): mixed
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     * @return Contact
     */
    public function setPhone($phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getService(): mixed
    {
        return $this->service;
    }

    /**
     * @param mixed $service
     * @return Contact
     */
    public function setService($service): static
    {
        $this->service = $service;
        return $this;
    }
}