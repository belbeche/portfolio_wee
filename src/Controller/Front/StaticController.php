<?php

namespace App\Controller\Front;


use App\Service\StaticService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StaticController extends AbstractController
{
    /**
     * @Route("/static", name="front_static_index")
     */
    public function index() : response
    {
        return $this->render('front/static/index.html.twig', [
            'staticValue' => StaticService::staticValue(),
        ]);
    }
}