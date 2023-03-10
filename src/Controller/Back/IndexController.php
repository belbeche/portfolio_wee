<?php

namespace App\Controller\Back;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/admin/index", name="back_index")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(): Response
    {
        return $this->render('back/index/index.html.twig');
    }
}
