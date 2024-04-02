<?php

namespace App\Entity;

use App\Entity\Devis;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="Il existe déjà un compte avec cet email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="doctrine.uuid_generator")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
    * @var string The hashed password
    * @ORM\Column(type="string")
    */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=255, nullable="true")
     */
    private ?string $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable="true")
     */
    private ?string $prenom;

    /**
     * @ORM\Column(type="string", length=10, nullable="true")
     */
    private ?string $civility;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resetTokenExpireAt;

    /**
     * @ORM\Column(type="string")
     */
    private $avatar;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="sender")
     */
    private $sentMessages;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="receiver")
     */
    private $receivedMessages;

    /**
     * @ORM\OneToMany(targetEntity=Devis::class, mappedBy="user", orphanRemoval=true)
     */
    private $devis;

    /**
     * @ORM\Column(type="string")
     */
    private $username;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\OneToMany (targetEntity=Comment::class, mappedBy="user", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="user", orphanRemoval=true)
     */
    private Collection $articles;

    public function __construct()
    {
        $this->devis = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->avatar = 'uploads/avatar/support0.svg';
        $this->roles = ['ROLE_USER'];

        $this->articles = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->date = new DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        $this->username = $this->email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @ORM\OneToMany(
     *     targetEntity="UserLike",
     *     mappedBy="user",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    private Collection $likes;

    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCivility()
    {
        return $this->civility;
    }

    /**
     * @param mixed $civility
     * @return User
     */
    public function setCivility($civility)
    {
        $this->civility = $civility;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetTokenExpireAt(): ?\DateTimeInterface
    {
        return $this->resetTokenExpireAt;
    }

    public function setResetTokenExpireAt(?\DateTimeInterface $resetTokenExpireAt): self
    {
        $this->resetTokenExpireAt = $resetTokenExpireAt;
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function __toString()
    {
        return $this->email; //
    }

    /**
     * @return Collection|Devis[]
     */
    public function getDevis(): Collection
    {
        return $this->devis;
    }

    public function addDevis(Devis $devis): self
    {
        if (!$this->devis->contains($devis)) {
            $this->devis[] = $devis;
            $devis->setUser($this);
        }

        return $this;
    }

    public function removeDevis(Devis $devis): self
    {
        if ($this->devis->removeElement($devis)) {
            // set the owning side to null (unless already changed)
            if ($devis->getUser() === $this) {
                $devis->setUser(null);
            }
        }

        return $this;
    }

    public function addSentMessage(Message $sentMessage): static
    {
        if (!$this->sentMessages->contains($sentMessage)) {
            $this->sentMessages->add($sentMessage);
            $sentMessage->setSender($this);
        }

        return $this;
    }

    public function removeSentMessage(Message $sentMessage): static
    {
        if ($this->sentMessages->removeElement($sentMessage)) {
            // set the owning side to null (unless already changed)
            if ($sentMessage->getSender() === $this) {
                $sentMessage->setSender(null);
            }
        }

        return $this;
    }

    public function addReceivedMessage(Message $receivedMessage): static
    {
        if (!$this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages->add($receivedMessage);
            $receivedMessage->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Message $receivedMessage): static
    {
        if ($this->receivedMessages->removeElement($receivedMessage)) {
            // set the owning side to null (unless already changed)
            if ($receivedMessage->getReceiver() === $this) {
                $receivedMessage->setReceiver(null);
            }
        }

        return $this;
    }

    public function addDevi(Devis $devi): static
    {
        if (!$this->devis->contains($devi)) {
            $this->devis->add($devi);
            $devi->setUser($this);
        }

        return $this;
    }

    public function removeDevi(Devis $devi): static
    {
        if ($this->devis->removeElement($devi)) {
            // set the owning side to null (unless already changed)
            if ($devi->getUser() === $this) {
                $devi->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     * @return User
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;
    }

    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
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
     * @return User
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

    /**
     * @return ArrayCollection
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    /**
     * @param ArrayCollection $articles
     * @return User
     */
    public function setArticles(ArrayCollection $articles): User
    {
        $this->articles = $articles;
        return $this;
    }

    public function addArticle(Article $article)
    {
        $this->articles[] = $article;
    }

    public function removeArticle(Article $article)
    {
        $this->articles->removeElement($article);
    }
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}