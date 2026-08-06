<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Le laboratoire : les outils gratuits, rassembles sur une page.
 *
 * L'interet n'est pas la page en soi, c'est ce qu'elle change dans le
 * parcours. Un visiteur qui arrive sur un site de prestataire n'a rien a y
 * faire tant qu'il n'a pas de projet. Avec des outils, il a une raison de
 * venir avant d'avoir un besoin, et une raison de revenir. Le jour ou le
 * besoin arrive, il sait deja a qui ecrire.
 *
 * Regle que je m'impose ici : on n'annonce que ce qui fonctionne. Une page
 * d'outils remplie de « bientot disponible » produit l'effet inverse de
 * celui recherche.
 */
class OutilsController extends AbstractController
{
    /**
     * @Route("/outils", name="front_outils", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('front/outils/index.html.twig');
    }

    /**
     * Le simulateur d'economies.
     *
     * Aucune donnee n'est envoyee au serveur : tout se calcule dans le
     * navigateur. Quelqu'un qui saisit sa masse salariale n'a pas envie
     * qu'elle parte quelque part, et le lui promettre sans que ce soit vrai
     * serait la pire facon de commencer une relation.
     *
     * @Route("/outils/economies", name="front_economies", methods={"GET"})
     */
    public function economies(): Response
    {
        return $this->render('front/outils/economies.html.twig');
    }
}
