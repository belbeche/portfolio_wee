<?php

namespace App\Entity;

use App\Repository\PushSubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Un abonnement aux notifications push d'un navigateur.
 *
 * @ORM\Entity(repositoryClass=PushSubscriptionRepository::class)
 * @ORM\Table(name="push_subscription", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="uniq_push_endpoint", columns={"endpoint_hash"})
 * })
 */
class PushSubscription
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * L'URL unique fournie par le navigateur (FCM, Mozilla...).
     *
     * @ORM\Column(type="text")
     */
    private string $endpoint = '';

    /**
     * Empreinte de l'endpoint, pour l'unicite en base (TEXT n'est pas indexable).
     *
     * @ORM\Column(type="string", length=64)
     */
    private string $endpointHash = '';

    /** @ORM\Column(type="string", length=255) */
    private string $p256dh = '';

    /** @ORM\Column(type="string", length=255) */
    private string $auth = '';

    /** @ORM\Column(type="string", length=180, nullable=true) */
    private ?string $userEmail = null;

    /** @ORM\Column(type="datetime") */
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getEndpoint(): string { return $this->endpoint; }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;
        $this->endpointHash = hash('sha256', $endpoint);

        return $this;
    }

    public function getEndpointHash(): string { return $this->endpointHash; }

    public function getP256dh(): string { return $this->p256dh; }
    public function setP256dh(string $p256dh): self { $this->p256dh = $p256dh; return $this; }

    public function getAuth(): string { return $this->auth; }
    public function setAuth(string $auth): self { $this->auth = $auth; return $this; }

    public function getUserEmail(): ?string { return $this->userEmail; }
    public function setUserEmail(?string $userEmail): self { $this->userEmail = $userEmail; return $this; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }
}
