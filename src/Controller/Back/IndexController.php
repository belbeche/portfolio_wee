<?php

namespace App\Controller\Back;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Tableau de bord de l'administration.
 *
 * L'ancienne version affichait un titre et un lien de retour a l'accueil.
 * Un tableau de bord doit repondre a une seule question quand on l'ouvre :
 * qu'est-ce qui demande mon attention maintenant.
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/admin/index", name="back_index")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('back/index/index.html.twig', [
            'derniersDevis' => $this->latest($em, 'App\Entity\Devis', 6),
            'derniersRappels' => $this->latest($em, 'App\Entity\CallbackRequest', 6),
            'derniersTickets' => $this->latest($em, 'App\Entity\Ticket', 6),
            'derniersContacts' => $this->latest($em, 'App\Entity\Contact', 5),
        ]);
    }

    /**
     * Les N derniers enregistrements d'une entite, sans planter si la table
     * n'existe pas encore.
     */
    private function latest(EntityManagerInterface $em, string $class, int $limit): array
    {
        try {
            return $em->createQuery(sprintf('SELECT e FROM %s e ORDER BY e.id DESC', $class))
                ->setMaxResults($limit)
                ->getResult();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
