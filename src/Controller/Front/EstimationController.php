<?php

namespace App\Controller\Front;

use App\Service\Tarifs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * L'estimateur de budget, en direct.
 *
 * Aucune base de donnees, aucune adresse demandee, aucun compte : le visiteur
 * manipule, voit le total bouger, et repart avec un ordre de grandeur. Le seul
 * appel a l'action, en bas, emmene le resultat vers le formulaire de devis
 * deja pre-rempli, pour qu'il n'ait pas a tout ressaisir.
 */
class EstimationController extends AbstractController
{
    /**
     * @Route("/estimation", name="front_estimation", methods={"GET"})
     */
    public function index(Tarifs $tarifs): Response
    {
        return $this->render('front/estimation/index.html.twig', [
            'bases' => $tarifs->basesChiffrees(),
            'options' => $tarifs->optionsChiffrees(),
            'marge' => Tarifs::MARGE,
            'tjm' => $tarifs->tjm(),
        ]);
    }
}
