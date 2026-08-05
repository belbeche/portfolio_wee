<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Un envoi de la sequence de prospection, et ce qu'il est devenu.
 *
 * Sans cette table, la prospection est aveugle : on sait qu'on a envoye, on
 * ne sait pas si quelqu'un a lu. Or c'est la seule information qui permet de
 * decider quoi faire ensuite. Un prospect qui a ouvert trois fois et n'a pas
 * repondu merite un appel ; un prospect qui n'a jamais ouvert merite une
 * autre adresse ou un autre objet.
 *
 * @ORM\Entity(repositoryClass=\App\Repository\ProspectEnvoiRepository::class)
 * @ORM\Table(name="prospect_envoi")
 */
class ProspectEnvoi
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Prospect::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?Prospect $prospect = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $etape = 1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $sujet = '';

    /**
     * Jeton unique de cet envoi : il sert d'adresse au pixel de suivi et aux
     * liens. Un jeton par envoi, jamais l'identifiant du prospect, sinon
     * l'adresse du pixel revelerait qui est suivi.
     *
     * @ORM\Column(type="string", length=32, unique=true)
     */
    private string $jeton = '';

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $envoyeLe = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $ouvertLe = null;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $ouvertures = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $cliqueLe = null;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $clics = 0;

    public function __construct()
    {
        $this->envoyeLe = new \DateTime();
        $this->jeton = bin2hex(random_bytes(12));
    }

    public function getId(): ?int { return $this->id; }

    public function getProspect(): ?Prospect { return $this->prospect; }
    public function setProspect(?Prospect $prospect): self { $this->prospect = $prospect; return $this; }

    public function getEtape(): int { return $this->etape; }
    public function setEtape(int $etape): self { $this->etape = $etape; return $this; }

    public function getSujet(): string { return $this->sujet; }
    public function setSujet(string $sujet): self { $this->sujet = $sujet; return $this; }

    public function getJeton(): string { return $this->jeton; }

    public function getEnvoyeLe(): ?\DateTimeInterface { return $this->envoyeLe; }

    public function getOuvertLe(): ?\DateTimeInterface { return $this->ouvertLe; }
    public function getOuvertures(): int { return $this->ouvertures; }

    public function getCliqueLe(): ?\DateTimeInterface { return $this->cliqueLe; }
    public function getClics(): int { return $this->clics; }

    public function marquerOuvert(): void
    {
        if (null === $this->ouvertLe) {
            $this->ouvertLe = new \DateTime();
        }
        ++$this->ouvertures;
    }

    public function marquerClique(): void
    {
        if (null === $this->cliqueLe) {
            $this->cliqueLe = new \DateTime();
        }
        ++$this->clics;

        // Un clic implique une lecture : certains clients de messagerie
        // bloquent les images mais suivent les liens.
        if (null === $this->ouvertLe) {
            $this->ouvertLe = new \DateTime();
            ++$this->ouvertures;
        }
    }

    /** Niveau d'interet, du plus froid au plus chaud. */
    public function getTemperature(): string
    {
        if ($this->clics > 0) {
            return 'chaud';
        }
        if ($this->ouvertures >= 3) {
            return 'tiede';
        }
        if ($this->ouvertures > 0) {
            return 'lu';
        }

        return 'froid';
    }
}
