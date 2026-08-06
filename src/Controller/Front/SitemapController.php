<?php

namespace App\Controller\Front;

use App\Entity\Post;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Plan du site, genere a la volee.
 *
 * Les moteurs de recherche le lisent pour connaitre toutes les pages sans
 * avoir a les deviner. Il se met a jour tout seul : une realisation ou un
 * article ajoute dans le back office y apparait immediatement.
 */
class SitemapController extends AbstractController
{
    /**
     * @Route("/sitemap.xml", name="front_sitemap", defaults={"_format"="xml"})
     */
    public function index(EntityManagerInterface $em): Response
    {
        $base = 'https://walidbelbeche.fr';
        $urls = [];

        // Pages fixes, de la plus importante a la moins consultee
        $pages = [
            ['front_home', 1.0, 'weekly'],
            ['front_services', 0.9, 'monthly'],
            ['front_project', 0.9, 'weekly'],
            ['front_devis_new', 0.9, 'monthly'],
            // L'audit est une porte d'entree a part entiere : il repond a une
            // recherche que les gens font vraiment, « tester mon site ».
            ['front_audit', 0.9, 'monthly'],
            // « combien coute un site » est une des recherches les plus
            // frequentes du secteur : autant y repondre soi-meme.
            ['front_estimation', 0.9, 'monthly'],
            ['front_outils', 0.8, 'monthly'],
            ['front_economies', 0.8, 'monthly'],
            ['front_commencer', 0.8, 'monthly'],
            ['front_methode', 0.7, 'monthly'],
            ['front_agences', 0.7, 'monthly'],
            ['front_contact', 0.7, 'yearly'],
            ['blog_index', 0.7, 'weekly'],
            ['front_referral', 0.6, 'monthly'],
            ['front_mentions', 0.2, 'yearly'],
            ['front_cguv', 0.2, 'yearly'],
            ['front_pdc', 0.2, 'yearly'],
        ];

        foreach ($pages as [$route, $priorite, $frequence]) {
            try {
                $urls[] = [
                    'loc' => $base.$this->generateUrl($route),
                    'priority' => $priorite,
                    'changefreq' => $frequence,
                    'lastmod' => null,
                ];
            } catch (\Throwable $e) {
                // Une route renommee ne doit pas casser tout le plan du site.
                continue;
            }
        }

        // Realisations
        foreach ($em->getRepository(Project::class)->findAll() as $projet) {
            $urls[] = [
                'loc' => $base.$this->generateUrl('front_project_show', ['id' => $projet->getId()]),
                'priority' => 0.8,
                'changefreq' => 'monthly',
                'lastmod' => $projet->getUpdatedAt() ?: $projet->getCreatedAt(),
            ];
        }

        // Articles publies uniquement : un brouillon ne doit pas etre indexe
        $articles = $em->getRepository(Post::class)->findBy(['status' => 'published']);
        foreach ($articles as $article) {
            if (!$article->getSlug()) {
                continue;
            }
            $urls[] = [
                'loc' => $base.$this->generateUrl('blog_show', ['slug' => $article->getSlug()]),
                'priority' => 0.6,
                'changefreq' => 'yearly',
                'lastmod' => $article->getCreatedAt(),
            ];
        }

        $reponse = $this->render('front/sitemap.xml.twig', ['urls' => $urls]);
        $reponse->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $reponse;
    }
}
