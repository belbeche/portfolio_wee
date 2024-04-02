<?php

namespace App\Controller\Front;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MaintenanceController extends AbstractController
{
    /**
     * @Route("/", name="front_maintenance")
     */
    public function maintenance()
    {
        return new Response(
            $this->renderView('front/maintenance/index.html.twig'),
            Response::HTTP_SERVICE_UNAVAILABLE
        );
    }
}