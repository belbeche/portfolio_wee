<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Uid\Uuid;



/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 */
class Article
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="doctrine.uuid_generator")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $content;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active = false;

    /**
     * @ORM\Column(type="string", length=255, nullable="true")
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isPublished;

    /**
     * @ORM\Column(type="datetime", nullable="true")
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="article", orphanRemoval=true)
     */
    private $commentaires;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class, inversedBy="articles")
     */
    private $categories;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?User $user;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    private Collection $images;

    /**
     * @ORM\OneToMany(
     *     targetEntity="UserLike",
     *     mappedBy="article",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    private ?Collection $likes;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull
     */
    private $article_id;

    public function __construct()
    {
        $this->user = null;
        $this->categories = null;
        $this->active = true;
        $this->commentaires = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id ? $this->id->toRfc4122() : null;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug(): mixed
    {
        return $this->title;
    }

    public function setSlug($slug): static
    {
        $this->title = $title;
        return $this;
    }

    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function setCommentaires(ArrayCollection $commentaires): Article
    {
        $this->commentaires = $commentaires;
        return $this;
    }

    public function addCommentaire(Comment $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setArticle($this);
        }

        return $this;
    }

    public function removeCommentaire(Comment $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getArticle() === $this) {
                $commentaire->setArticle(null);
            }
        }

        return $this;
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function setCategories(ArrayCollection $categories)
    {
        $this->categories = $categories;

        return $this;
    }

    public function addCategory(Category $category)
    {
        $this->categories[] = $category;
    }

    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getImages(): ArrayCollection|Collection
    {
        return $this->images;
    }

    public function setImages(ArrayCollection $images): Article
    {
        $this->images = $images;
        return $this;
    }

    public function addImage(Image $image)
    {
        $this->images[] = $image;
    }

    public function removeImage(Image $image)
    {
        $this->images->removeElement($image);
    }

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        dump($this->publishedAt);
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param Collection $likes
     * @return Article
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;
        return $this;
    }

    /**
     * @param UserLike $like
     * @return void
     */
    public function addLike(UserLike $like)
    {
        $this->likes[] = $like;
    }

    /**
     * @param UserLike $like
     * @return void
     */
    public function removeLike(UserLike $like)
    {
        $this->likes->removeElement($like);
    }

    public function getArticleId(): ?int
    {
        return $this->article_id;
    }

    public function setArticleId(int $articleId): self
    {
        $this->article_id = $articleId;

        return $this;
    }
}
