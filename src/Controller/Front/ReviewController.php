<?php

namespace App\Controller\Front;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    /**
     * @Route("/avis", name="front_review", methods={"GET", "POST"})
     */
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        ReviewRepository $reviews,
        RateLimiterFactory $contactFormLimiter
    ): Response {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $limiter = $contactFormLimiter->create($request->getClientIp() ?? 'anon');
            if (!$limiter->consume(1)->isAccepted()) {
                $this->addFlash('error', 'Trop de tentatives, reessayez dans quelques minutes.');

                return $this->redirectToRoute('front_review');
            }

            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', $request->getLocale() === 'en'
                ? 'Thank you. Your review will appear after moderation.'
                : 'Merci. Votre avis sera publie apres moderation.');

            return $this->redirectToRoute('front_review');
        }

        return $this->render('front/review/index.html.twig', [
            'form' => $form->createView(),
            'reviews' => $reviews->findApproved(12),
        ]);
    }
}
