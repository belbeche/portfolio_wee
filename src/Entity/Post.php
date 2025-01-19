<?php

// src/Entity/Post.php
namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class Post
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
    private ?string $title = null;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $content = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Section", mappedBy="post", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $sections;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CodeBlock", mappedBy="post", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $codeBlocks;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $createdAt = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Category $category = null;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->codeBlocks = new ArrayCollection(); 
        $this->createdAt = new DateTime();
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of title
     */ 
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @return  self
     */ 
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of slug
     */ 
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the value of slug
     *
     * @return  self
     */ 
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get the value of content
     */ 
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @return  self
     */ 
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    // Getters et setters pour les sections
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setPost($this);
        }
        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->removeElement($section)) {
            if ($section->getPost() === $this) {
                $section->setPost(null);
            }
        }
        return $this;
    }

    /**
     * Get the value of codeBlocks
     */
    public function getCodeBlocks(): Collection
    {
        return $this->codeBlocks;
    }

    public function addCodeBlock(CodeBlock $codeBlock): self
    {
        if (!$this->codeBlocks->contains($codeBlock)) {
            $this->codeBlocks[] = $codeBlock;
            $codeBlock->setPost($this);
        }

        return $this;
    }

    public function removeCodeBlock(CodeBlock $codeBlock): self
    {
        if ($this->codeBlocks->removeElement($codeBlock)) {
            if ($codeBlock->getPost() === $this) {
                $codeBlock->setPost(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of createdAt
     */ 
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */ 
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }
}
