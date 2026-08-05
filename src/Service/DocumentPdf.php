<?php

namespace App\Service;

use App\Entity\Devis;
use App\Entity\Facture;
use Mpdf\Mpdf;
use Twig\Environment;

/**
 * Fabrique les PDF de devis et de factures.
 *
 * Tout passe par ici pour une raison : le jour ou le rendu deraille, il n'y a
 * qu'un seul endroit a regarder. Le garde-fou sur le nombre de pages est ne
 * d'un incident reel, un devis d'une page parti en 1362 pages.
 */
class DocumentPdf
{
    /**
     * Au-dela, on refuse le document.
     *
     * Un devis ou une facture tient sur quelques pages. Passer ce seuil ne
     * veut pas dire « document long », ca veut dire « mPDF boucle ». Mieux
     * vaut une erreur claire qu'un fichier de 1362 pages envoye au client.
     */
    public const PAGES_MAX = 12;

    private Environment $twig;
    private Settings $settings;

    public function __construct(Environment $twig, Settings $settings)
    {
        $this->twig = $twig;
        $this->settings = $settings;
    }

    /** @return array{contenu: string, nom: string} */
    public function pourDevis(Devis $devis): array
    {
        $contexte = $this->contexteCommun();
        $tauxDefaut = (float) $contexte['tauxTva'];

        $lignes = [];
        foreach ($devis->getLignes() as $ligne) {
            $lignes[] = [
                'designation' => $ligne->getDesignation(),
                'detail' => $ligne->getDetail(),
                'quantite' => (float) $ligne->getQuantite(),
                'unite' => $ligne->getUniteLabel(),
                'prixUnitaire' => (float) $ligne->getPrixUnitaire(),
                'remise' => (float) $ligne->getRemise(),
                'tauxTva' => (float) $ligne->getTauxTva(),
                'totalHt' => $ligne->getTotalHt(),
            ];
        }

        // Devis anterieurs a la mise en place des lignes : on reconstitue une
        // ligne unique a partir de l'ancien champ prix, pour qu'ils restent
        // consultables et imprimables.
        if ([] === $lignes) {
            $ht = (float) ($devis->getPrix() ?: 0);
            $lignes[] = [
                'designation' => $devis->getTypeDeSiteWeb() ?: 'Prestation de developpement',
                'detail' => $devis->getResponse() ?: $devis->getDescriptionProjet(),
                'quantite' => 1.0,
                'unite' => 'Forfait',
                'prixUnitaire' => $ht,
                'remise' => 0.0,
                'tauxTva' => $contexte['franchise'] ? 0.0 : $tauxDefaut,
                'totalHt' => $ht,
            ];
        }

        [$totalHt, $tvaParTaux, $totalTva] = $this->totaliser($lignes);

        $validite = (int) ($this->settings->get('devis_validite_jours', '30') ?: 30);
        $emisLe = \DateTime::createFromFormat('U', (string) $devis->getCreatedAt()->getTimestamp());

        $html = $this->twig->render('pdf/document.html.twig', array_merge($contexte, [
            'type' => 'devis',
            'titre' => 'DEVIS',
            'reference' => $devis->getReference(),
            'emisLe' => $emisLe,
            'echeanceLe' => (clone $emisLe)->modify('+'.$validite.' days'),
            'echeanceLabel' => 'Valable jusqu\'au',
            'clientNom' => $this->nomClient($devis),
            'clientEmail' => $devis->getEmail(),
            'clientAdresse' => null,
            'projet' => $devis->getTypeDeSiteWeb(),
            'lignes' => $lignes,
            'totalHt' => $totalHt,
            'tvaParTaux' => $tvaParTaux,
            'totalTva' => $totalTva,
            'totalTtc' => round($totalHt + $totalTva, 2),
            'validite' => $validite,
            'statut' => null,
            'regle' => null,
            'reste' => null,
        ]));

        return ['contenu' => $this->fabriquer($html), 'nom' => 'Devis-'.$devis->getReference().'.pdf'];
    }

    /** @return array{contenu: string, nom: string} */
    public function pourFacture(Facture $facture): array
    {
        $contexte = $this->contexteCommun();

        $lignes = $facture->getLignes();
        [$totalHt, $tvaParTaux, $totalTva] = $this->totaliser($lignes);

        $html = $this->twig->render('pdf/document.html.twig', array_merge($contexte, [
            'type' => 'facture',
            'titre' => 'FACTURE',
            'reference' => $facture->getNumero(),
            'emisLe' => $facture->getEmiseLe(),
            'echeanceLe' => $facture->getEcheanceLe(),
            'echeanceLabel' => 'A regler avant le',
            'clientNom' => $facture->getClientNom(),
            'clientEmail' => $facture->getClientEmail(),
            'clientAdresse' => $facture->getClientAdresse(),
            'projet' => $facture->getDevis() ? $facture->getDevis()->getTypeDeSiteWeb() : null,
            'lignes' => $lignes,
            'totalHt' => $totalHt,
            'tvaParTaux' => $tvaParTaux,
            'totalTva' => $totalTva,
            'totalTtc' => round($totalHt + $totalTva, 2),
            'validite' => null,
            'statut' => $facture->getStatutLabel(),
            'regle' => $facture->getMontantRegle(),
            'reste' => $facture->getResteADevoir(),
            'moyen' => $facture->getMoyenLabel(),
            'devisOrigine' => $facture->getDevis() ? $facture->getDevis()->getReference() : null,
        ]));

        return ['contenu' => $this->fabriquer($html), 'nom' => 'Facture-'.$facture->getNumero().'.pdf'];
    }

    // ------------------------------------------------------------- interne

    /**
     * @param array<int, array<string, mixed>> $lignes
     *
     * @return array{0: float, 1: array<string, array{base: float, montant: float}>, 2: float}
     */
    private function totaliser(array $lignes): array
    {
        $totalHt = 0.0;
        $paliers = [];

        foreach ($lignes as $ligne) {
            $ht = (float) ($ligne['totalHt'] ?? 0);
            $taux = number_format((float) ($ligne['tauxTva'] ?? 0), 2, '.', '');
            $totalHt += $ht;

            if (!isset($paliers[$taux])) {
                $paliers[$taux] = ['base' => 0.0, 'montant' => 0.0];
            }
            $paliers[$taux]['base'] += $ht;
            $paliers[$taux]['montant'] += $ht * (float) $taux / 100;
        }

        ksort($paliers);

        $totalTva = 0.0;
        foreach ($paliers as $taux => $palier) {
            $paliers[$taux]['base'] = round($palier['base'], 2);
            $paliers[$taux]['montant'] = round($palier['montant'], 2);
            $totalTva += $paliers[$taux]['montant'];
        }

        return [round($totalHt, 2), $paliers, round($totalTva, 2)];
    }

    /** @return array<string, mixed> */
    private function contexteCommun(): array
    {
        $numeroTva = trim((string) $this->settings->get('entreprise_tva', ''));
        $taux = trim((string) $this->settings->get('devis_tva_taux', '20'));

        return [
            'nomEntreprise' => $this->settings->get('entreprise_nom', 'Walid BELBECHE'),
            'adresse' => $this->settings->get('entreprise_adresse', ''),
            'siren' => $this->settings->get('entreprise_siren', ''),
            'numeroTva' => $numeroTva,
            'telephone' => $this->settings->get('contact_phone', ''),
            'courriel' => 'contact@walidbelbeche.fr',
            'iban' => $this->settings->get('entreprise_iban', ''),
            'penalites' => $this->settings->get('devis_penalites', "trois fois le taux d'interet legal"),
            'tauxTva' => $taux,
            'franchise' => '' === $numeroTva || '0' === $taux,
        ];
    }

    private function nomClient(Devis $devis): string
    {
        $client = $devis->getUser();
        if (null === $client) {
            return (string) $devis->getEmail();
        }

        $nom = trim(((string) $client->getPrenom()).' '.((string) $client->getNom()));

        return '' !== $nom ? $nom : (string) $devis->getEmail();
    }

    /**
     * Le rendu mPDF, avec le garde-fou.
     */
    private function fabriquer(string $html): string
    {
        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 12,
            'margin_bottom' => 18,
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_footer' => 8,
            'default_font' => 'dejavusans',
            'default_font_size' => 9.5,
        ]);

        $mpdf->SetTitle('Document');
        $mpdf->WriteHTML($html);

        $pages = (int) $mpdf->page;
        if ($pages > self::PAGES_MAX) {
            throw new \RuntimeException(sprintf(
                'Rendu interrompu : %d pages generees alors qu\'un document de ce type en fait au plus %d. '
                .'C\'est le signe que le gabarit ne tient pas dans la largeur de la page. Rien n\'a ete envoye.',
                $pages,
                self::PAGES_MAX
            ));
        }

        return $mpdf->Output('', 'S');
    }
}
