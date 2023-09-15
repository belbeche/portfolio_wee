<?php

namespace App\Controller\Front;

use App\Entity\Category;
use App\Entity\Devis;
use App\Entity\Project;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjetsController extends AbstractController
{

    /**
     * @Route("/realisations", name="front_project")
     * @Route("/realisations/{category}", name="front_project_by_category")
     */
    public function index(EntityManagerInterface $entityManager, string $category = null): Response
    {
        if ($category) {
            $projects = $entityManager->getRepository(Project::class)->findBy(['category' => $category]);
        } else {
            $projects = $entityManager->getRepository(Project::class)->findAll();
        }

        return $this->render('front/projets/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    /**
     * @Route("/projet/realisation/{id}", name="front_project_show")
     */
    public function show(EntityManagerInterface $entityManager, Project $project): Response
    {
        $project = $entityManager->getRepository(Project::class)->findOneBy(['id' => $project]);
        return $this->render('front/projets/show.html.twig', [
            'project' => $project
        ]);
    }
}
