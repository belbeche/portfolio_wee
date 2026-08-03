<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * La porte d'entree du site : trois chemins selon le besoin.
 * Un visiteur qui ne sait pas ou aller atterrit ici.
 */
class CommencerController extends AbstractController
{
    /**
     * @Route("/commencer", name="front_commencer")
     */
    public function index(): Response
    {
        return $this->render('front/commencer/index.html.twig');
    }
}
