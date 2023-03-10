<?php

namespace App\Controller\Front;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjetsController extends AbstractController
{
    /**
     * @Route("/realisations", name="front_project")
     */
    public function show(EntityManagerInterface $entityManager): Response
    {
        return $this->render('front/projets/index.html.twig');
    }
}
