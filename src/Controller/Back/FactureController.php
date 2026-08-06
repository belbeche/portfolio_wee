<?php

namespace App\Controller\Back;

use App\Entity\Devis;
use App\Entity\DevisLigne;
use App\Entity\Facture;
use App\Form\DevisLignesType;
use App\Repository\FactureRepository;
use App\Service\DocumentPdf;
use App\Service\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Le chiffrage des devis et la facturation.
 *
 * @IsGranted("ROLE_ADMIN")
 */
class FactureController extends AbstractController
{
    // ------------------------------------------------------- lignes de devis

    /**
     * Le decompte detaille d'un devis : c'est ici qu'on chiffre.
     *
     * @Route("/admin/devis/{id}/lignes", name="back_devis_lignes", methods={"GET", "POST"})
     */
    public function lignes(Request $request, Devis $devis, EntityManagerInterface $em, Settings $settings): Response
    {
        // Un devis vide s'ouvre avec une premiere ligne prete a remplir :
        // personne n'a envie de cliquer « ajouter » avant de commencer.
        if ($devis->getLignes()->isEmpty()) {
            $ligne = (new DevisLigne())
                ->setDesignation($this->libelleProjet($devis->getTypeDeSiteWeb()))
                ->setPrixUnitaire($devis->getPrix() ?: 0)
                ->setTauxTva($settings->get('devis_tva_taux', '20') ?: '20');
            $devis->addLigne($ligne);
        }

        $form = $this->createForm(DevisLignesType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $position = 0;
            foreach ($devis->getLignes() as $ligne) {
                $ligne->setDevis($devis);
                $ligne->setPosition($position++);
            }

            // L'ancien champ prix reste alimente : les listes, les statistiques
            // et les devis anterieurs continuent de fonctionner sans migration.
            $devis->setPrix((int) round($devis->getTotalHt()));

            $em->flush();
            $this->addFlash('success', sprintf(
                'Chiffrage enregistré : %s lignes, %s € HT, %s € TTC.',
                count($devis->getLignes()),
                number_format($devis->getTotalHt(), 2, ',', ' '),
                number_format($devis->getTotalTtc(), 2, ',', ' ')
            ));

            return $this->redirectToRoute('back_devis_lignes', ['id' => $devis->getId()]);
        }

        return $this->render('back/facture/lignes.html.twig', [
            'devis' => $devis,
            'form' => $form->createView(),
        ]);
    }

    /**
     * La cle technique du formulaire, en francais lisible.
     *
     * Sans cette traduction, la premiere ligne du devis s'appelait
     * « application_cross_plateforme » : un identifiant interne, sous les yeux
     * du client, sur un document contractuel.
     */
    private function libelleProjet(?string $cle): string
    {
        $libelles = [
            'site_vitrine' => 'Site vitrine',
            'site_real_estate' => "Site d'annonces immobilieres",
            'site_e-commerce' => 'Boutique en ligne',
            'site_blog' => 'Blog professionnel',
            'site_portfolio' => 'Portfolio',
            'site_forum' => 'Forum communautaire',
            'site_info_magazine' => "Site d'information",
            'application_cross_plateforme' => 'Application web et mobile',
            'systeme_gestion_contenu' => 'Systeme de gestion de contenu',
            'intranet' => "Intranet d'entreprise",
            'autre' => 'Prestation sur mesure',
        ];

        return $libelles[(string) $cle] ?? 'Prestation de developpement';
    }

    /**
     * Apercu du devis en PDF, sans rien envoyer a personne.
     *
     * @Route("/admin/devis/{id}/apercu", name="back_devis_preview", methods={"GET"})
     */
    public function apercuDevis(Devis $devis, DocumentPdf $pdf): Response
    {
        try {
            $document = $pdf->pourDevis($devis);
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('back_devis_lignes', ['id' => $devis->getId()]);
        }

        return new Response($document['contenu'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$document['nom'].'"',
        ]);
    }

    // ------------------------------------------------------------- factures

    /**
     * @Route("/admin/factures", name="back_facture_index", methods={"GET"})
     */
    public function index(FactureRepository $factures): Response
    {
        $liste = $factures->recentes();

        $encaisse = 0.0;
        $attendu = 0.0;
        $retard = 0.0;
        foreach ($liste as $facture) {
            if (Facture::STATUT_ANNULEE === $facture->getStatut()) {
                continue;
            }
            $encaisse += $facture->getMontantRegle();
            $attendu += $facture->getResteADevoir();
            if ($facture->estEnRetard()) {
                $retard += $facture->getResteADevoir();
            }
        }

        return $this->render('back/facture/index.html.twig', [
            'factures' => $liste,
            'encaisse' => $encaisse,
            'attendu' => $attendu,
            'retard' => $retard,
        ]);
    }

    /**
     * Emet une facture a partir d'un devis.
     *
     * Les lignes sont recopiees : une facture est immuable, elle ne peut pas
     * dependre d'un devis que l'on modifiera peut-etre demain.
     *
     * @Route("/admin/devis/{id}/facturer", name="back_facture_from_devis", methods={"POST"})
     */
    public function depuisDevis(Request $request, Devis $devis, EntityManagerInterface $em, FactureRepository $factures, Settings $settings): Response
    {
        if (!$this->isCsrfTokenValid('facturer_'.$devis->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expirée, réessaie.');

            return $this->redirectToRoute('back_devis_lignes', ['id' => $devis->getId()]);
        }

        if ($devis->getLignes()->isEmpty() && !$devis->getPrix()) {
            $this->addFlash('error', "Ce devis n'est pas chiffré : ajoute au moins une ligne avant de facturer.");

            return $this->redirectToRoute('back_devis_lignes', ['id' => $devis->getId()]);
        }

        $facture = new Facture();
        $facture->setNumero($factures->prochainNumero());
        $facture->setDevis($devis);
        $facture->setClient($devis->getUser());

        $client = $devis->getUser();
        $nom = $client ? trim(((string) $client->getPrenom()).' '.((string) $client->getNom())) : '';
        $facture->setClientNom('' !== $nom ? $nom : (string) $devis->getEmail());
        $facture->setClientEmail($devis->getEmail());

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

        if ([] === $lignes) {
            $ht = (float) $devis->getPrix();
            $taux = (float) ($settings->get('devis_tva_taux', '20') ?: 0);
            $lignes[] = [
                'designation' => $devis->getTypeDeSiteWeb() ?: 'Prestation de developpement',
                'detail' => $devis->getResponse(),
                'quantite' => 1.0,
                'unite' => 'Forfait',
                'prixUnitaire' => $ht,
                'remise' => 0.0,
                'tauxTva' => $taux,
                'totalHt' => $ht,
            ];
        }

        $totalHt = 0.0;
        $totalTva = 0.0;
        foreach ($lignes as $ligne) {
            $totalHt += $ligne['totalHt'];
            $totalTva += $ligne['totalHt'] * $ligne['tauxTva'] / 100;
        }

        $facture->setLignes($lignes);
        $facture->setTotalHt($totalHt);
        $facture->setTotalTva($totalTva);
        $facture->setTotalTtc($totalHt + $totalTva);
        $facture->setStatut(Facture::STATUT_EMISE);

        $em->persist($facture);
        $em->flush();

        $this->addFlash('success', sprintf('Facture %s émise pour %s € TTC.', $facture->getNumero(), number_format($facture->getTotalTtc(), 2, ',', ' ')));

        return $this->redirectToRoute('back_facture_show', ['id' => $facture->getId()]);
    }

    /**
     * @Route("/admin/factures/{id}", name="back_facture_show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(Facture $facture): Response
    {
        return $this->render('back/facture/show.html.twig', ['facture' => $facture]);
    }

    /**
     * Enregistre un encaissement.
     *
     * @Route("/admin/factures/{id}/paiement", name="back_facture_payment", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function paiement(Request $request, Facture $facture, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('paiement_'.$facture->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expirée, réessaie.');

            return $this->redirectToRoute('back_facture_show', ['id' => $facture->getId()]);
        }

        $action = (string) $request->request->get('action');

        if ('solder' === $action) {
            $facture->setMontantRegle($facture->getTotalTtc());
        } elseif ('annuler' === $action) {
            $facture->setStatut(Facture::STATUT_ANNULEE);
        } else {
            $montant = (float) str_replace(',', '.', (string) $request->request->get('montant'));
            $facture->setMontantRegle($facture->getMontantRegle() + max(0, $montant));
        }

        $moyen = (string) $request->request->get('moyen');
        if ('' !== $moyen) {
            $facture->setMoyenPaiement($moyen);
        }

        $facture->rafraichirStatut();
        $em->flush();

        $this->addFlash('success', sprintf(
            '%s. Réglé : %s € sur %s €. Reste : %s €.',
            $facture->getStatutLabel(),
            number_format($facture->getMontantRegle(), 2, ',', ' '),
            number_format($facture->getTotalTtc(), 2, ',', ' '),
            number_format($facture->getResteADevoir(), 2, ',', ' ')
        ));

        return $this->redirectToRoute('back_facture_show', ['id' => $facture->getId()]);
    }

    /**
     * @Route("/admin/factures/{id}/pdf", name="back_facture_pdf", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function pdf(Facture $facture, DocumentPdf $pdf): Response
    {
        $document = $pdf->pourFacture($facture);

        return new Response($document['contenu'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$document['nom'].'"',
        ]);
    }

    /**
     * @Route("/admin/factures/{id}/envoyer", name="back_facture_send", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function envoyer(Request $request, Facture $facture, DocumentPdf $pdf, MailerInterface $mailer, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('envoyer_'.$facture->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expirée, réessaie.');

            return $this->redirectToRoute('back_facture_show', ['id' => $facture->getId()]);
        }

        if (!$facture->getClientEmail()) {
            $this->addFlash('error', "Aucune adresse e-mail sur cette facture.");

            return $this->redirectToRoute('back_facture_show', ['id' => $facture->getId()]);
        }

        try {
            $document = $pdf->pourFacture($facture);

            $courriel = (new Email())
                ->from('contact@walidbelbeche.fr')
                ->to($facture->getClientEmail())
                ->bcc('wbelbeche.s@gmail.com')
                ->subject('Facture '.$facture->getNumero())
                ->text(sprintf(
                    "Bonjour,\n\nVous trouverez ci-joint la facture %s d'un montant de %s euros TTC, a regler avant le %s.\n\nMerci de votre confiance.\n\nWalid Belbeche\nwalidbelbeche.fr",
                    $facture->getNumero(),
                    number_format($facture->getTotalTtc(), 2, ',', ' '),
                    $facture->getEcheanceLe()->format('d/m/Y')
                ))
                ->attach($document['contenu'], $document['nom'], 'application/pdf');

            $mailer->send($courriel);

            if (Facture::STATUT_BROUILLON === $facture->getStatut()) {
                $facture->setStatut(Facture::STATUT_EMISE);
                $em->flush();
            }

            $this->addFlash('success', 'Facture envoyée à '.$facture->getClientEmail().'.');
        } catch (\Throwable $e) {
            $this->addFlash('error', "Envoi impossible : ".$e->getMessage());
        }

        return $this->redirectToRoute('back_facture_show', ['id' => $facture->getId()]);
    }
}
