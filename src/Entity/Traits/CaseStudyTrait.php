<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Champs « étude de cas » d'une réalisation.
 *
 * Une réalisation ne convertit pas parce qu'elle est jolie, mais parce qu'un
 * visiteur s'y reconnaît. D'où l'ordre imposé : contexte, problème, approche,
 * résultat, preuve.
 *
 * Tous les champs sont nullable : les réalisations existantes restent
 * valides et s'enrichissent progressivement.
 */
trait CaseStudyTrait
{
    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $sector = null;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $context = null;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $problem = null;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $approach = null;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $result = null;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $duration = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $stack = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $deliverables = null;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $testimonial = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $testimonialAuthor = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $storeUrlIos = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $storeUrlAndroid = null;

    /**
     * Public visé : 'agence' ou 'direct'. Permet de filtrer les réalisations
     * montrées selon la porte d'entrée empruntée par le visiteur.
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $audience = null;

    /**
     * Les 3 études de cas mises en avant sur l'accueil.
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $featured = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $position = null;

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function setSector(?string $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(?string $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getProblem(): ?string
    {
        return $this->problem;
    }

    public function setProblem(?string $problem): self
    {
        $this->problem = $problem;

        return $this;
    }

    public function getApproach(): ?string
    {
        return $this->approach;
    }

    public function setApproach(?string $approach): self
    {
        $this->approach = $approach;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getStack(): array
    {
        return $this->stack ?? [];
    }

    public function setStack(?array $stack): self
    {
        $this->stack = $stack;

        return $this;
    }

    public function getDeliverables(): array
    {
        return $this->deliverables ?? [];
    }

    public function setDeliverables(?array $deliverables): self
    {
        $this->deliverables = $deliverables;

        return $this;
    }

    public function getTestimonial(): ?string
    {
        return $this->testimonial;
    }

    public function setTestimonial(?string $testimonial): self
    {
        $this->testimonial = $testimonial;

        return $this;
    }

    public function getTestimonialAuthor(): ?string
    {
        return $this->testimonialAuthor;
    }

    public function setTestimonialAuthor(?string $testimonialAuthor): self
    {
        $this->testimonialAuthor = $testimonialAuthor;

        return $this;
    }

    public function getStoreUrlIos(): ?string
    {
        return $this->storeUrlIos;
    }

    public function setStoreUrlIos(?string $storeUrlIos): self
    {
        $this->storeUrlIos = $storeUrlIos;

        return $this;
    }

    public function getStoreUrlAndroid(): ?string
    {
        return $this->storeUrlAndroid;
    }

    public function setStoreUrlAndroid(?string $storeUrlAndroid): self
    {
        $this->storeUrlAndroid = $storeUrlAndroid;

        return $this;
    }

    public function getAudience(): ?string
    {
        return $this->audience;
    }

    public function setAudience(?string $audience): self
    {
        $this->audience = $audience;

        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Une étude de cas n'est publiable que si les quatre blocs narratifs
     * sont remplis. Sert à n'afficher le format long que quand il a du sens.
     */
    public function isCaseStudyComplete(): bool
    {
        return !empty($this->context)
            && !empty($this->problem)
            && !empty($this->approach)
            && !empty($this->result);
    }
}
