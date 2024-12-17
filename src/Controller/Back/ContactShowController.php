<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactShowController extends AbstractController
{
    #[Route('/back/contact/show', name: 'app_back_contact_show')]
    public function index(): Response
    {
        return $this->render('back/contact_show/index.html.twig', [
            'controller_name' => 'ContactShowController',
        ]);
    }
}
