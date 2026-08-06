<?php

namespace App\Entity;


use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DevisRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DevisRepository::class)
 */
class Devis
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="doctrine.uuid_generator")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type_de_site_web;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description_projet;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="integer", nullable="true")
     */
    private $prix;

    /**
     * Une seule declaration de colonne, et elle dit la verite : l'adresse
     * est facultative. Il y en avait deux empilees ici, dont la seconde
     * contredisait la premiere.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email(message="L'adresse email '{{ value }}' n'est pas valide.")
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $statut;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $response;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="devis", orphanRemoval=true, cascade={"remove"})
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="devis", cascade={"remove"})
     */
    private $tickets;

    /**
    * @ORM\ManyToOne(targetEntity=User::class, inversedBy="devis")
    * @ORM\JoinColumn(nullable=true)
    */
    private $user;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\AttenteDesignWeb", inversedBy="devis", cascade={"persist"})
     * @ORM\JoinTable(name="devis_attente_design_web")
     */
    private Collection $attentesDesignWeb;

    /**
     * Le decompte detaille, ligne par ligne.
     *
     * @ORM\OneToMany(targetEntity=DevisLigne::class, mappedBy="devis", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    private Collection $lignes;

    public function __construct()
    {
        $this->created_at = new DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        $this->messages = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->attentesDesignWeb = new ArrayCollection();
        $this->lignes = new ArrayCollection();
    }

    /** @return Collection<int, DevisLigne> */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(DevisLigne $ligne): self
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setDevis($this);
        }

        return $this;
    }

    public function removeLigne(DevisLigne $ligne): self
    {
        if ($this->lignes->removeElement($ligne) && $ligne->getDevis() === $this) {
            $ligne->setDevis(null);
        }

        return $this;
    }

    /**
     * Total hors taxes.
     *
     * Tant qu'aucune ligne n'existe, on retombe sur l'ancien champ prix :
     * les devis deja envoyes continuent de s'afficher correctement.
     */
    public function getTotalHt(): float
    {
        if ($this->lignes->isEmpty()) {
            return (float) ($this->prix ?: 0);
        }

        $total = 0.0;
        foreach ($this->lignes as $ligne) {
            $total += $ligne->getTotalHt();
        }

        return round($total, 2);
    }

    public function getMontantTva(): float
    {
        if ($this->lignes->isEmpty()) {
            return 0.0; // le taux est alors applique par le gabarit
        }

        $total = 0.0;
        foreach ($this->lignes as $ligne) {
            $total += $ligne->getMontantTva();
        }

        return round($total, 2);
    }

    public function getTotalTtc(): float
    {
        return round($this->getTotalHt() + $this->getMontantTva(), 2);
    }

    /**
     * La TVA regroupee par taux : un devis peut melanger 20 % et 0 %, et la
     * facture doit alors detailler chaque base d'imposition separement.
     *
     * @return array<string, array{base: float, montant: float}>
     */
    public function getTvaParTaux(): array
    {
        $paliers = [];
        foreach ($this->lignes as $ligne) {
            $taux = number_format((float) $ligne->getTauxTva(), 2, '.', '');
            if (!isset($paliers[$taux])) {
                $paliers[$taux] = ['base' => 0.0, 'montant' => 0.0];
            }
            $paliers[$taux]['base'] += $ligne->getTotalHt();
            $paliers[$taux]['montant'] += $ligne->getMontantTva();
        }
        ksort($paliers);

        return $paliers;
    }

    /** La reference lisible, celle que l'on cite au telephone. */
    public function getReference(): string
    {
        return 'DEV-'.$this->created_at->format('Y').'-'.strtoupper(substr((string) $this->id, 0, 6));
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTypeDeSiteWeb(): ?string
    {
        return $this->type_de_site_web;
    }

    public function setTypeDeSiteWeb(string $type_de_site_web): self
    {
        $this->type_de_site_web = $type_de_site_web;

        return $this;
    }

    public function getDescriptionProjet(): ?string
    {
        return $this->description_projet;
    }

    public function setDescriptionProjet(string $description_projet): self
    {
        $this->description_projet = $description_projet;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    /**
     * @param DateTimeImmutable $created_at
     * @return Devis
     */
    public function setCreatedAt(DateTimeImmutable $created_at): Devis
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(?int $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        // Le champ est facultatif dans le formulaire de devis : refuser null
        // ici renvoyait une erreur 500 au visiteur au lieu d'enregistrer sa
        // demande. Perdre un prospect sur un champ vide coute cher.
        $this->email = $email;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTickets(): ArrayCollection
    {
        return $this->tickets;
    }

    /**
     * @param ArrayCollection $tickets
     * @return Devis
     */
    public function setTickets(ArrayCollection $tickets): Devis
    {
        $this->tickets = $tickets;
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }
    public function setEmailFromUser(?User $user): void
    {
        if ($user) {
            // Utilisateur connecté, utilisez son adresse email
            $this->email = $user->getEmail();
        }
    }

    public function __toString()
    {
        return $this->type_de_site_web;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setDevis($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getDevis() === $this) {
                $message->setDevis(null);
            }
        }

        return $this;
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

    public function getAttentesDesignWeb(): Collection
    {
        return $this->attentesDesignWeb;
    }

    public function setAttentesDesignWeb(Collection $attentesDesignWeb): Devis
    {
        $this->attentesDesignWeb = $attentesDesignWeb;
        return $this;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setDevis($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getDevis() === $this) {
                $ticket->setDevis(null);
            }
        }

        return $this;
    }

    public function addAttentesDesignWeb(AttenteDesignWeb $attentesDesignWeb): static
    {
        if (!$this->attentesDesignWeb->contains($attentesDesignWeb)) {
            $this->attentesDesignWeb->add($attentesDesignWeb);
        }

        return $this;
    }

    public function removeAttentesDesignWeb(AttenteDesignWeb $attentesDesignWeb): static
    {
        $this->attentesDesignWeb->removeElement($attentesDesignWeb);

        return $this;
    }
}
