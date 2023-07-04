<?php

namespace App\Controller\Front;

use App\Entity\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="front_home")
     */
    public function index(): Response
    {
        return $this->render('front/home/index.html.twig');
    }
}
