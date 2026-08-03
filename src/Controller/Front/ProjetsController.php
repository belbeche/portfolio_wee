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
    public function index(EntityManagerInterface $entityManager, Request $request, ?string $category = null): Response
    {
        $repo = $entityManager->getRepository(Project::class);

        // Tous les projets, les mis en avant d'abord, puis par position.
        $projects = $repo->createQueryBuilder('p')
            ->orderBy('p.featured', 'DESC')
            ->addOrderBy('p.position', 'ASC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()->getResult();

        // La liste fermee des secteurs reellement presents en base.
        $sectors = [];
        $sectorCounts = [];
        foreach ($projects as $project) {
            $sector = trim((string) $project->getSector());
            if ('' !== $sector) {
                if (!in_array($sector, $sectors, true)) {
                    $sectors[] = $sector;
                }
                $sectorCounts[$sector] = ($sectorCounts[$sector] ?? 0) + 1;
            }
        }
        sort($sectors);
        $totalCount = count($projects);

        // Filtre par secteur : /realisations?secteur=Association
        $current = trim((string) ($request->query->get('secteur') ?? $category ?? ''));
        if ('' !== $current) {
            $projects = array_values(array_filter(
                $projects,
                fn (Project $p) => 0 === strcasecmp(trim((string) $p->getSector()), $current)
            ));
        }

        return $this->render('front/projets/index.html.twig', [
            'projects' => $projects,
            'sectors' => $sectors,
            'sectorCounts' => $sectorCounts,
            'totalCount' => $totalCount,
            'currentSector' => $current,
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
