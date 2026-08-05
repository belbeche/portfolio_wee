<?php

namespace App\Controller\Back;

use App\Entity\Prospect;
use App\Repository\ProspectEnvoiRepository;
use App\Repository\ProspectRepository;
use App\Service\SequenceProspection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Le pilotage de la sequence de prospection.
 *
 * @IsGranted("ROLE_ADMIN")
 */
class SequenceController extends AbstractController
{
    /**
     * @Route("/admin/prospection", name="back_sequence", methods={"GET"})
     */
    public function index(ProspectRepository $prospects, ProspectEnvoiRepository $envois, SequenceProspection $sequence): Response
    {
        $tous = $prospects->findAll();

        $aFaire = [];
        $parEtape = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0];

        foreach ($tous as $prospect) {
            $parEtape[$prospect->getEtape()] = ($parEtape[$prospect->getEtape()] ?? 0) + 1;

            $due = $sequence->etapeDue($prospect);
            if (null !== $due && false === strpos((string) $prospect->getEmail(), 'a-completer')) {
                $aFaire[] = ['prospect' => $prospect, 'etape' => $due];
            }
        }

        // Les prospects qui ont ouvert passent devant : ce sont eux qui
        // convertissent, et c'est la seule information qui merite de changer
        // l'ordre de la file.
        usort($aFaire, static fn ($a, $b) => $b['etape'] <=> $a['etape']);

        return $this->render('back/prospect/sequence.html.twig', [
            'aFaire' => $aFaire,
            'parEtape' => $parEtape,
            'stats' => $envois->statistiques(),
            'interesses' => $envois->interesses(20),
            'etapes' => SequenceProspection::ETAPES,
            'cadence' => SequenceProspection::CADENCE,
            'total' => count($tous),
        ]);
    }

    /**
     * Envoie l'etape suivante a un prospect. Appelee une par une par la page,
     * exactement comme la vague : une requete, un envoi, jamais de rafale.
     *
     * @Route("/admin/prospection/envoyer/{id}", name="back_sequence_send", methods={"POST"})
     */
    public function envoyer(Request $request, Prospect $prospect, SequenceProspection $sequence): JsonResponse
    {
        if (!$this->isCsrfTokenValid('sequence', (string) $request->request->get('_token'))) {
            return new JsonResponse(['ok' => false, 'message' => 'Session expiree, recharge la page.'], 403);
        }

        $request->getSession()->save();

        $etape = $sequence->etapeDue($prospect);
        if (null === $etape) {
            return new JsonResponse(['ok' => false, 'message' => 'Rien a envoyer a ce prospect aujourd\'hui.']);
        }

        $erreur = $sequence->envoyer($prospect, $etape);

        return new JsonResponse([
            'ok' => null === $erreur,
            'etape' => $etape,
            'societe' => (string) ($prospect->getCompany() ?: $prospect->getEmail()),
            'message' => $erreur,
        ]);
    }

    /**
     * Relance le diagnostic du site d'un prospect.
     *
     * @Route("/admin/prospection/diagnostic/{id}", name="back_sequence_diagnostic", methods={"POST"})
     */
    public function diagnostic(Request $request, Prospect $prospect, SequenceProspection $sequence): Response
    {
        if (!$this->isCsrfTokenValid('diagnostic_'.$prospect->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expiree, reessaie.');

            return $this->redirectToRoute('back_sequence');
        }

        $resultat = $sequence->diagnostiquer($prospect, true);

        $this->addFlash('success', sprintf(
            '%s : %s',
            $prospect->getCompany() ?: $prospect->getEmail(),
            $resultat['resume'] ?? 'analyse terminee'
        ));

        return $this->redirectToRoute('back_prospect_notes', ['id' => $prospect->getId()]);
    }

    /**
     * Envoi d'essai vers sa propre adresse, avec un vrai prospect en exemple.
     *
     * @Route("/admin/prospection/essai/{etape}", name="back_sequence_preview", methods={"POST"}, requirements={"etape"="[1-4]"})
     */
    public function essai(int $etape, Request $request, ProspectRepository $prospects, SequenceProspection $sequence, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('sequence_essai', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expiree, reessaie.');

            return $this->redirectToRoute('back_sequence');
        }

        // Un prospect fictif, jamais enregistre : l'essai ne doit toucher
        // aucune fiche reelle ni consommer une etape.
        $exemple = new Prospect();
        $exemple->setEmail((string) $this->getUser()->getUserIdentifier())
            ->setCompany('Agence Exemple')
            ->setContactName('Marie Dupont')
            ->setCity('Rouen')
            ->setWebsite('http://exemple-sans-https.fr');

        $exemple->setDiagnostic([
            'url' => 'http://exemple-sans-https.fr',
            'joignable' => true,
            'ms' => 3200,
            'constats' => [
                ['cle' => 'https', 'titre' => 'Pas de certificat', 'gravite' => 'haute',
                 'phrase' => "votre site s'ouvre en http : les navigateurs affichent « Non securise » a vos visiteurs, et Google declasse ces pages depuis 2018"],
                ['cle' => 'vitesse', 'titre' => 'Page lente', 'gravite' => 'haute',
                 'phrase' => 'votre page met 3,2 secondes a repondre depuis un acces standard'],
                ['cle' => 'description', 'titre' => 'Pas de description', 'gravite' => 'moyenne',
                 'phrase' => "votre page n'a pas de meta description"],
            ],
            'resume' => 'Exemple',
        ]);

        $erreur = $sequence->envoyer($exemple, $etape, true);

        if (null === $erreur) {
            $this->addFlash('success', sprintf(
                'Etape %d envoyee en essai a %s. Aucun prospect reel n\'a ete touche.',
                $etape,
                $this->getUser()->getUserIdentifier()
            ));
        } else {
            $this->addFlash('error', 'Essai impossible : '.$erreur);
        }

        return $this->redirectToRoute('back_sequence');
    }
}
