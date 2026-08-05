<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Une facture.
 *
 * Point juridique qui gouverne la conception : une facture est immuable une
 * fois emise. On ne peut donc pas la calculer a la volee depuis le devis, qui
 * lui reste modifiable. Les lignes sont recopiees dans un instantane JSON au
 * moment de l'emission, et les totaux sont figes en base.
 *
 * La numerotation est une suite continue sans trou, exigee par le fisc. Elle
 * est attribuee au moment de l'emission, jamais avant.
 *
 * @ORM\Entity(repositoryClass=\App\Repository\FactureRepository::class)
 * @ORM\Table(name="facture")
 */
class Facture
{
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_EMISE = 'emise';
    public const STATUT_PARTIELLE = 'partielle';
    public const STATUT_PAYEE = 'payee';
    public const STATUT_ANNULEE = 'annulee';

    public const STATUTS = [
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_EMISE => 'Emise',
        self::STATUT_PARTIELLE => 'Payee en partie',
        self::STATUT_PAYEE => 'Payee',
        self::STATUT_ANNULEE => 'Annulee',
    ];

    public const MOYENS = [
        'virement' => 'Virement',
        'carte' => 'Carte bancaire',
        'cheque' => 'Cheque',
        'especes' => 'Especes',
        'autre' => 'Autre',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     */
    private string $numero = '';

    /**
     * @ORM\ManyToOne(targetEntity=Devis::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?Devis $devis = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?User $client = null;

    /**
     * Nom et adresse du client figes a l'emission : si le compte est modifie
     * plus tard, la facture doit continuer de dire ce qu'elle disait.
     *
     * @ORM\Column(type="string", length=255)
     */
    private string $clientNom = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $clientEmail = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $clientAdresse = null;

    /**
     * @ORM\Column(type="date")
     */
    private ?\DateTimeInterface $emiseLe = null;

    /**
     * @ORM\Column(type="date")
     */
    private ?\DateTimeInterface $echeanceLe = null;

    /**
     * Instantane des lignes : designation, quantite, unite, prix, TVA.
     *
     * @ORM\Column(type="json")
     */
    private array $lignes = [];

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private string $totalHt = '0.00';

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private string $totalTva = '0.00';

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private string $totalTtc = '0.00';

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private string $montantRegle = '0.00';

    /**
     * @ORM\Column(type="string", length=20)
     */
    private string $statut = self::STATUT_BROUILLON;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private ?string $moyenPaiement = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $regleeLe = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $notes = null;

    public function __construct()
    {
        $this->emiseLe = new \DateTime();
        $this->echeanceLe = (new \DateTime())->modify('+30 days');
    }

    public function getId(): ?int { return $this->id; }

    public function getNumero(): string { return $this->numero; }
    public function setNumero(string $numero): self { $this->numero = $numero; return $this; }

    public function getDevis(): ?Devis { return $this->devis; }
    public function setDevis(?Devis $devis): self { $this->devis = $devis; return $this; }

    public function getClient(): ?User { return $this->client; }
    public function setClient(?User $client): self { $this->client = $client; return $this; }

    public function getClientNom(): string { return $this->clientNom; }
    public function setClientNom(string $nom): self { $this->clientNom = $nom; return $this; }

    public function getClientEmail(): ?string { return $this->clientEmail; }
    public function setClientEmail(?string $email): self { $this->clientEmail = $email; return $this; }

    public function getClientAdresse(): ?string { return $this->clientAdresse; }
    public function setClientAdresse(?string $adresse): self { $this->clientAdresse = $adresse; return $this; }

    public function getEmiseLe(): ?\DateTimeInterface { return $this->emiseLe; }
    public function setEmiseLe(?\DateTimeInterface $date): self { $this->emiseLe = $date; return $this; }

    public function getEcheanceLe(): ?\DateTimeInterface { return $this->echeanceLe; }
    public function setEcheanceLe(?\DateTimeInterface $date): self { $this->echeanceLe = $date; return $this; }

    /** @return array<int, array<string, mixed>> */
    public function getLignes(): array { return $this->lignes; }
    /** @param array<int, array<string, mixed>> $lignes */
    public function setLignes(array $lignes): self { $this->lignes = $lignes; return $this; }

    public function getTotalHt(): float { return (float) $this->totalHt; }
    public function setTotalHt($v): self { $this->totalHt = (string) round((float) $v, 2); return $this; }

    public function getTotalTva(): float { return (float) $this->totalTva; }
    public function setTotalTva($v): self { $this->totalTva = (string) round((float) $v, 2); return $this; }

    public function getTotalTtc(): float { return (float) $this->totalTtc; }
    public function setTotalTtc($v): self { $this->totalTtc = (string) round((float) $v, 2); return $this; }

    public function getMontantRegle(): float { return (float) $this->montantRegle; }
    public function setMontantRegle($v): self { $this->montantRegle = (string) round((float) $v, 2); return $this; }

    public function getMoyenPaiement(): ?string { return $this->moyenPaiement; }
    public function setMoyenPaiement(?string $moyen): self { $this->moyenPaiement = $moyen; return $this; }
    public function getMoyenLabel(): ?string { return null === $this->moyenPaiement ? null : (self::MOYENS[$this->moyenPaiement] ?? $this->moyenPaiement); }

    public function getRegleeLe(): ?\DateTimeInterface { return $this->regleeLe; }
    public function setRegleeLe(?\DateTimeInterface $date): self { $this->regleeLe = $date; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): self { $this->notes = $notes; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): self { $this->statut = $statut; return $this; }
    public function getStatutLabel(): string { return self::STATUTS[$this->statut] ?? $this->statut; }

    /** Ce qu'il reste a encaisser. */
    public function getResteADevoir(): float
    {
        return round(max(0, $this->getTotalTtc() - $this->getMontantRegle()), 2);
    }

    /**
     * Le statut recalcule a partir des montants.
     *
     * On ne stocke pas « en retard » : c'est une consequence de la date, pas
     * une decision. Le stocker obligerait a repasser sur toutes les factures
     * chaque nuit pour rien.
     */
    public function rafraichirStatut(): void
    {
        if (self::STATUT_ANNULEE === $this->statut || self::STATUT_BROUILLON === $this->statut) {
            return;
        }

        if ($this->getMontantRegle() <= 0) {
            $this->statut = self::STATUT_EMISE;
        } elseif ($this->getResteADevoir() <= 0.004) {
            $this->statut = self::STATUT_PAYEE;
            if (null === $this->regleeLe) {
                $this->regleeLe = new \DateTime();
            }
        } else {
            $this->statut = self::STATUT_PARTIELLE;
        }
    }

    /** En retard : emise, pas soldee, et l'echeance est passee. */
    public function estEnRetard(): bool
    {
        if (in_array($this->statut, [self::STATUT_PAYEE, self::STATUT_ANNULEE, self::STATUT_BROUILLON], true)) {
            return false;
        }

        return null !== $this->echeanceLe && $this->echeanceLe < new \DateTime('today');
    }

    public function getJoursDeRetard(): int
    {
        if (!$this->estEnRetard()) {
            return 0;
        }

        return (int) (new \DateTime('today'))->diff($this->echeanceLe)->days;
    }

    /** Couleur d'affichage dans l'administration. */
    public function getTon(): string
    {
        if ($this->estEnRetard()) {
            return 'red';
        }

        return [
            self::STATUT_PAYEE => 'green',
            self::STATUT_PARTIELLE => 'gold',
            self::STATUT_EMISE => 'blue',
            self::STATUT_ANNULEE => 'muted',
        ][$this->statut] ?? 'muted';
    }
}
