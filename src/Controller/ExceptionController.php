<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ExceptionController extends AbstractController
{
    /**
     * @Route("/erreur", name="erreur")
     */
    public function showException(\Throwable $exception): Response
    {
        if ($exception instanceof NotFoundHttpException) {
            // Gérer les erreurs 404 Not Found
            return $this->render('erreur/404.html.twig');
        }

        // Gérer les autres types d'erreurs
        return $this->render('erreur/erreur.html.twig', [
            'exception' => $exception,
        ]);
    }
}
