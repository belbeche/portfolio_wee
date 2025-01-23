<?php

namespace App\Controller\Front;

use App\Entity\Project;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Gedmo\Translatable\TranslatableListener;

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
    public function show(EntityManagerInterface $entityManager, Project $project,Request $request): Response
    {
        $locale = $request->getLocale(); // Get the current locale (e.g., "en" or "fr")

        // Récupérer des projets connexes (même catégorie si possible)
        $relatedProjectsQuery = $entityManager->getRepository(Project::class)->createQueryBuilder('p')
            ->where('p.id != :currentProjectId')
            ->setParameter('currentProjectId', $project->getId())
            ->setMaxResults(4);

        // Ajouter une condition pour la catégorie uniquement si elle existe
        $firstCategory = $project->getCategories()->first();
        if ($firstCategory) {
            $relatedProjectsQuery->andWhere(':currentCategory MEMBER OF p.categories')
                ->setParameter('currentCategory', $firstCategory);
        }

        $relatedProjects = $relatedProjectsQuery->getQuery()->getResult();

        // Collecter les IDs déjà utilisés pour éviter les doublons
        $usedProjectIds = array_map(fn($p) => $p->getId(), $relatedProjects);
        $usedProjectIds[] = $project->getId();

        // Compléter avec des projets aléatoires si besoin
        if (count($relatedProjects) < 4) {
            $additionalProjects = $entityManager->getRepository(Project::class)->createQueryBuilder('p')
                ->where('p.id NOT IN (:usedProjectIds)')
                ->setParameter('usedProjectIds', $usedProjectIds)
                ->setMaxResults(4 - count($relatedProjects))
                ->getQuery()
                ->getResult();

            $relatedProjects = array_merge($relatedProjects, $additionalProjects);
        }

        // Set the desired locale for translations
        $project->setLocale($locale);

        return $this->render('front/projets/show.html.twig', [
            'project' => $project,
            'relatedProjects' => $relatedProjects,
        ]);
    }
}
