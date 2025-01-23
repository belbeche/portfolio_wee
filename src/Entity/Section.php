<?php

// src/Entity/Section.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Section
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private string $type; // 'content', 'code', ou 'media'

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $content = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $mediaUrl = null; // URL ou chemin du média

    /**
     * Propriété pour gérer le fichier temporairement (non mappée dans la base)
     * 
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={"image/jpeg", "image/png", "video/mp4"},
     *     mimeTypesMessage="Veuillez envoyer un fichier au format JPEG, PNG ou MP4."
     * )
     */
    private ?File $mediaFile = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $mediaType = null; // 'image' ou 'video'

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $language = null; // Langage de programmation pour les blocs de code

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="sections")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Post $post = null;

    // Getters et setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;
        return $this;
    }

    public function getMediaFile(): ?\Symfony\Component\HttpFoundation\File\File
    {
        return $this->mediaFile;
    }

    public function setMediaFile(?\Symfony\Component\HttpFoundation\File\File $mediaFile): self
    {
        $this->mediaFile = $mediaFile;
        return $this;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(?string $mediaType): self
    {
        $this->mediaType = $mediaType;
        return $this;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(?string $mediaUrl): self
    {
        $this->mediaUrl = $mediaUrl;
        return $this;
    }
}
