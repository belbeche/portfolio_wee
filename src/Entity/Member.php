<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MemberRepository::class)
 * @ORM\Table(name="member")
 */
class Member
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $lastname = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $firstname = null;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=255, nullable="true")
     */
    private ?string $more = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="string", length=255, nullable="true")
     */
    private ?string $link = null;

    /**
     * @ORM\Column(type="string", length=255, nullable="true")
     */
    private ?string $avatar = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private ?string $function;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string|null $lastname
     * @return Member
     */
    public function setLastname(?string $lastname): Member
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string|null $firstname
     * @return Member
     */
    public function setFirstname(?string $firstname): Member
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Member
     */
    public function setDescription(?string $description): Member
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMore(): ?string
    {
        return $this->more;
    }

    /**
     * @param string|null $more
     * @return Member
     */
    public function setMore(?string $more): Member
    {
        $this->more = $more;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return Member
     */
    public function setEmail(?string $email): Member
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string|null $link
     * @return Member
     */
    public function setLink(?string $link): Member
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * @param string|null $avatar
     * @return Member
     */
    public function setAvatar(?string $avatar): Member
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFunction(): ?string
    {
        return $this->function;
    }

    /**
     * @param string|null $function
     * @return Member
     */
    public function setFunction(?string $function): Member
    {
        $this->function = $function;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }
}
