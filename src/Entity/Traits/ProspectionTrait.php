<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Champs de pilotage de la prospection.
 *
 * Le principe : un prospect sans date de prochaine relance est un prospect
 * perdu. Tout le reste de ce trait sert cette seule idée.
 */
trait ProspectionTrait
{
    public const STATUS_TO_CONTACT = 'a_contacter';
    public const STATUS_CONTACTED = 'contacte';
    public const STATUS_FOLLOWED_UP = 'relance';
    public const STATUS_MEETING = 'rdv_pris';
    public const STATUS_PROPOSAL = 'proposition';
    public const STATUS_WON = 'gagne';
    public const STATUS_LOST = 'perdu';
    public const STATUS_OUT_OF_SCOPE = 'hors_cible';

    public const STATUSES = [
        self::STATUS_TO_CONTACT => 'À contacter',
        self::STATUS_CONTACTED => 'Contacté',
        self::STATUS_FOLLOWED_UP => 'Relancé',
        self::STATUS_MEETING => 'RDV pris',
        self::STATUS_PROPOSAL => 'Proposition envoyée',
        self::STATUS_WON => 'Gagné',
        self::STATUS_LOST => 'Perdu',
        self::STATUS_OUT_OF_SCOPE => 'Hors cible',
    ];

    /** Statuts pour lesquels une relance n'a plus de sens. */
    public const CLOSED_STATUSES = [
        self::STATUS_WON,
        self::STATUS_LOST,
        self::STATUS_OUT_OF_SCOPE,
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $company = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $contactName = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $phone = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $website = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $city = null;

    /**
     * Priorité issue de la qualification : P1 = pas d'offre mobile
     * (cible idéale), P2 = à qualifier, P3 = fait déjà du mobile.
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private ?string $priority = null;

    /**
     * @ORM\Column(type="string", length=30, options={"default": "a_contacter"})
     */
    private string $status = self::STATUS_TO_CONTACT;

    /**
     * D'où vient ce prospect : annuaire, LinkedIn, recommandation, site web…
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $source = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $lastContactedAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $nextFollowUpAt = null;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $followUpCount = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Ce que l'on a observe sur le site du prospect.
     *
     * C'est ce qui fait la difference entre « bonjour, je fais des sites » et
     * « votre site affiche Non securise dans Chrome depuis le 12 mars ». Le
     * premier finit dans la corbeille, le second obtient une reponse.
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $diagnostic = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $diagnostiqueLe = null;

    /**
     * Ou en est ce prospect dans la sequence : 0 pas encore contacte,
     * 1 a 4 les etapes deja envoyees.
     *
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $etape = 0;

    /** @return array<string, mixed>|null */
    public function getDiagnostic(): ?array
    {
        return $this->diagnostic;
    }

    /** @param array<string, mixed>|null $diagnostic */
    public function setDiagnostic(?array $diagnostic): self
    {
        $this->diagnostic = $diagnostic;
        $this->diagnostiqueLe = null === $diagnostic ? null : new \DateTime();

        return $this;
    }

    public function getDiagnostiqueLe(): ?\DateTimeInterface
    {
        return $this->diagnostiqueLe;
    }

    public function getEtape(): int
    {
        return $this->etape;
    }

    public function setEtape(int $etape): self
    {
        $this->etape = $etape;

        return $this;
    }

    /**
     * Les constats exploitables, du plus parlant au moins parlant.
     *
     * @return array<int, array{cle: string, titre: string, phrase: string, gravite: string}>
     */
    public function getConstats(): array
    {
        return $this->diagnostic['constats'] ?? [];
    }

    /** Le constat que l'on mettra en avant dans le premier e-mail. */
    public function getConstatPrincipal(): ?array
    {
        $constats = $this->getConstats();

        return $constats[0] ?? null;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactName(?string $contactName): self
    {
        $this->contactName = $contactName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getLastContactedAt(): ?\DateTimeInterface
    {
        return $this->lastContactedAt;
    }

    public function setLastContactedAt(?\DateTimeInterface $lastContactedAt): self
    {
        $this->lastContactedAt = $lastContactedAt;

        return $this;
    }

    public function getNextFollowUpAt(): ?\DateTimeInterface
    {
        return $this->nextFollowUpAt;
    }

    public function setNextFollowUpAt(?\DateTimeInterface $nextFollowUpAt): self
    {
        $this->nextFollowUpAt = $nextFollowUpAt;

        return $this;
    }

    public function getFollowUpCount(): int
    {
        return $this->followUpCount;
    }

    public function setFollowUpCount(int $followUpCount): self
    {
        $this->followUpCount = $followUpCount;

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

    /**
     * Enregistre un contact et programme automatiquement la relance suivante.
     *
     * La cadence J+4 puis J+10 n'est pas arbitraire : la majorité des réponses
     * arrivent après la première relance, et une troisième sollicitation
     * au-delà de J+10 ne produit quasiment plus rien.
     */
    public function registerContact(?\DateTimeInterface $when = null): self
    {
        $when = $when ?: new \DateTime('now', new \DateTimeZone('Europe/Paris'));

        $this->lastContactedAt = $when;
        ++$this->followUpCount;

        $this->status = 1 === $this->followUpCount
            ? self::STATUS_CONTACTED
            : self::STATUS_FOLLOWED_UP;

        $delay = [1 => '+4 days', 2 => '+10 days'][$this->followUpCount] ?? null;

        if (null === $delay) {
            // Au-delà de deux relances, on arrête : le prospect n'est pas mûr.
            $this->nextFollowUpAt = null;

            return $this;
        }

        $next = clone $when;
        $this->nextFollowUpAt = $next->modify($delay);

        return $this;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES, true);
    }

    public function isFollowUpDue(?\DateTimeInterface $now = null): bool
    {
        if (null === $this->nextFollowUpAt || $this->isClosed()) {
            return false;
        }

        return $this->nextFollowUpAt <= ($now ?: new \DateTime());
    }
}
