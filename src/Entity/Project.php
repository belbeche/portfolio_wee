<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255)
     */
    private ?string $title;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text")
     */
    private ?string $description;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="datetime")
     */
    private ?\DateTime $created_at;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="datetime")
     */
    private ?\DateTime $updated_at;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255)
     */
    private ?string $category;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="project", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $images;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="projects", cascade={"persist"})
     * @ORM\JoinTable(name="project_category")
     */
    private Collection $categories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $link;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $client;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $developer;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    /**
     * @ORM\OneToMany(targetEntity="Gedmo\Translatable\Entity\Translation", mappedBy="object", cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->created_at = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $this->categories = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): self
    {
        $this->updated_at = $updated_at;

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

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setProject($this);
        }
        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProject() === $this) {
                $image->setProject(null);
            }
        }

        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param Collection $categories
     * @return Project
     */
    public function setCategories(Collection $categories): Project
    {
        $this->categories = $categories;
        return $this;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
                $category->addProject($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            $category->removeProject($this);
        }

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get the value of client
     *
     * @return ?string
     */
    public function getClient(): ?string
    {
        return $this->client;
    }

    /**
     * Set the value of client
     *
     * @param ?string $client
     *
     * @return self
     */
    public function setClient(?string $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the value of developer
     *
     * @return ?string
     */
    public function getDeveloper(): ?string
    {
        return $this->developer;
    }

    /**
     * Set the value of developer
     *
     * @param ?string $developer
     *
     * @return self
     */
    public function setDeveloper(?string $developer): self
    {
        $this->developer = $developer;

        return $this;
    }

    /**
     * Get the value of translations
     */ 
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Set the value of translations
     *
     * @return  self
     */ 
    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }

    /**
     * Get the value of locale
     */ 
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the value of locale
     *
     * @return  self
     */ 
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
