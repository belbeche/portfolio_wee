<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ContactRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="doctrine.uuid_generator")
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
     * @ORM\Column(type="string", length=180)
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
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private mixed $content;

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

    /**
     * @return mixed
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     * @return Contact
     */
    public function setContent(mixed $content): Contact
    {
        $this->content = $content;
        return $this;
    }
}