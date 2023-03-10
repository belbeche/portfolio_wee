<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DevisController extends AbstractController
{
    /**
     * @Route("/admin/devis", name="back_devis")
     */
    public function index(): Response
    {
        return $this->render('back/devis/index.html.twig');
    }
}
