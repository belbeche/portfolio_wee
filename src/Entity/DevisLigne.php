<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Une ligne de devis : une prestation, sa quantite, son prix unitaire.
 *
 * Un devis serieux ne se resume pas a un montant global. Le client doit
 * pouvoir lire ce qu'il achete ligne par ligne, et c'est aussi une mention
 * obligatoire : « decompte detaille, en quantite et en prix, de chaque
 * prestation ».
 *
 * @ORM\Entity(repositoryClass=\App\Repository\DevisLigneRepository::class)
 * @ORM\Table(name="devis_ligne")
 */
class DevisLigne
{
    /** Unites proposees dans le formulaire. */
    public const UNITES = [
        'forfait' => 'Forfait',
        'heure' => 'Heure',
        'jour' => 'Jour',
        'mois' => 'Mois',
        'unite' => 'Unite',
        'licence' => 'Licence',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Devis::class, inversedBy="lignes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?Devis $devis = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $designation = '';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $detail = null;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private string $quantite = '1.00';

    /**
     * @ORM\Column(type="string", length=20)
     */
    private string $unite = 'forfait';

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private string $prixUnitaire = '0.00';

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private string $tauxTva = '20.00';

    /**
     * Remise en pourcentage appliquee a cette ligne.
     *
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private string $remise = '0.00';

    /**
     * @ORM\Column(type="integer")
     */
    private int $position = 0;

    public function getId(): ?int { return $this->id; }

    public function getDevis(): ?Devis { return $this->devis; }
    public function setDevis(?Devis $devis): self { $this->devis = $devis; return $this; }

    public function getDesignation(): string { return $this->designation; }
    public function setDesignation(string $designation): self { $this->designation = $designation; return $this; }

    public function getDetail(): ?string { return $this->detail; }
    public function setDetail(?string $detail): self { $this->detail = $detail; return $this; }

    public function getQuantite(): string { return $this->quantite; }
    public function setQuantite($quantite): self { $this->quantite = (string) $quantite; return $this; }

    public function getUnite(): string { return $this->unite; }
    public function setUnite(string $unite): self { $this->unite = $unite; return $this; }
    public function getUniteLabel(): string { return self::UNITES[$this->unite] ?? $this->unite; }

    public function getPrixUnitaire(): string { return $this->prixUnitaire; }
    public function setPrixUnitaire($prix): self { $this->prixUnitaire = (string) $prix; return $this; }

    public function getTauxTva(): string { return $this->tauxTva; }
    public function setTauxTva($taux): self { $this->tauxTva = (string) $taux; return $this; }

    public function getRemise(): string { return $this->remise; }
    public function setRemise($remise): self { $this->remise = (string) $remise; return $this; }

    public function getPosition(): int { return $this->position; }
    /**
     * Accepte null et le ramene a zero.
     *
     * Le champ « ordre d'affichage » est facultatif dans le formulaire, et
     * un champ facultatif laisse passer null. La signature refusait ce null
     * et la page tombait en erreur 500 au moment d'enregistrer. Une position
     * absente veut dire « en premier », pas « plantage ».
     */
    public function setPosition(?int $position): self { $this->position = $position ?? 0; return $this; }

    /** Total hors taxes de la ligne, remise deduite. */
    public function getTotalHt(): float
    {
        $brut = (float) $this->quantite * (float) $this->prixUnitaire;

        return round($brut * (1 - (float) $this->remise / 100), 2);
    }

    public function getMontantTva(): float
    {
        return round($this->getTotalHt() * (float) $this->tauxTva / 100, 2);
    }

    public function getTotalTtc(): float
    {
        return round($this->getTotalHt() + $this->getMontantTva(), 2);
    }
}
