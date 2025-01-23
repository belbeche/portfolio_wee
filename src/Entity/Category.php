<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
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
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Project", mappedBy="categories", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $projects;

    /**
     * @ORM\ManyToMany(targetEntity=Subject::class, mappedBy="categories", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $Subjects;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255)
     */
    private ?string $slug = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="category")
     */
    private Collection $posts;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->Subjects = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

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

    /**
    * @return Collection|Project[]
    */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->addCategory($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            $project->removeCategory($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Subject>
     */
    public function getSubjects(): Collection
    {
        return $this->Subjects;
    }

    public function addSubject(Subject $subject): static
    {
        if (!$this->Subjects->contains($subject)) {
            $this->Subjects->add($subject);
            $subject->addCategory($this);
        }

        return $this;
    }

    public function removeSubject(Subject $subject): static
    {
        if ($this->Subjects->removeElement($subject)) {
            $subject->removeCategory($this);
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }
}
