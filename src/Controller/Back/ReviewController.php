<?php

namespace App\Controller\Back;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/avis")
 * @IsGranted("ROLE_ADMIN")
 */
class ReviewController extends AbstractController
{
    /**
     * @Route("", name="back_review_index")
     */
    public function index(ReviewRepository $reviews): Response
    {
        return $this->render('back/review/index.html.twig', [
            'reviews' => $reviews->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    /**
     * @Route("/{id}/basculer", name="back_review_toggle", methods={"POST"})
     */
    public function toggle(Review $review, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('review'.$review->getId(), (string) $request->request->get('_token'))) {
            $review->setApproved(!$review->isApproved());
            $em->flush();
        }

        return $this->redirectToRoute('back_review_index');
    }

    /**
     * @Route("/{id}/supprimer", name="back_review_delete", methods={"POST"})
     */
    public function delete(Review $review, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('review'.$review->getId(), (string) $request->request->get('_token'))) {
            $em->remove($review);
            $em->flush();
        }

        return $this->redirectToRoute('back_review_index');
    }
}
