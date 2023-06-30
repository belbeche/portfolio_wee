<?php

namespace App\Controller\Front;

use App\Entity\Devis;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjetsController extends AbstractController
{

    /**
     * @Route("/realisations", name="front_project")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {

        $projects = $entityManager->getRepository(Project::class)->findAll();
        return $this->render('front/projets/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    /**
     * @Route("/realisations/{id}", name="front_project_show")
     */
    public function show(EntityManagerInterface $entityManager, Project $project): Response
    {
        $project = $entityManager->getRepository(Project::class)->find($project);
        return $this->render('front/projets/show.html.twig', [
            'project' => $project,
        ]);
    }
}
