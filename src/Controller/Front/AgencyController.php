<?php

namespace App\Controller\Front;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Les pages de conversion.
 *
 * Elles répondent à deux publics qui n'ont ni les mêmes peurs ni le même
 * vocabulaire : les agences web qui cherchent un sous-traitant mobile, et
 * les clients finaux. Les mélanger sur les mêmes pages, c'est mal servir
 * les deux.
 */
class AgencyController extends AbstractController
{
    /**
     * Page de destination des e-mails de prospection agences.
     *
     * @Route("/agences", name="front_agences")
     */
    public function agences(EntityManagerInterface $em): Response
    {
        return $this->render('front/agences/index.html.twig', [
            'caseStudies' => $this->featuredCaseStudies($em),
        ]);
    }

    /**
     * La méthode de travail, documentée.
     *
     * C'est la page qui différencie vraiment : la peur numéro un d'une agence
     * qui sous-traite n'est pas le mauvais code, c'est de se retrouver à
     * porter le chaos devant son propre client.
     *
     * @Route("/methode", name="front_methode")
     */
    public function methode(): Response
    {
        return $this->render('front/agences/methode.html.twig');
    }

    /**
     * Les trois dernières études de cas publiables.
     *
     * @return Project[]
     */
    private function featuredCaseStudies(EntityManagerInterface $em): array
    {
        return $em->getRepository(Project::class)
            ->createQueryBuilder('p')
            ->andWhere('p.featured = :featured')
            ->setParameter('featured', true)
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.created_at', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
}
