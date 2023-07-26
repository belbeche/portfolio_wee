<?php

namespace App\Controller\Front;

use App\Entity\Devis;
use App\Form\DevisType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class DevisController extends AbstractController
{
    /**
     * @Route("/devis-en-ligne", name="front_devis_new")
     * @throws TransportExceptionInterface
     */
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            // Utilisateur non connecté, renvoyer vers la page d'inscription
            return $this->redirectToRoute('app_register');

        } else {
            // Utilisateur connecté, utilisez son adresse email
            $email = $currentUser->getEmail();
        }

        // Vérifier si l'utilisateur a déjà un devis en fonction de l'adresse email
        $existingDevis = $entityManager->getRepository(Devis::class)->findOneBy(['email' => $email]);

        if ($existingDevis) {
            // Si un devis existe avec cette adresse email, rediriger vers la page d'assistance
            return $this->redirectToRoute('front_assistance');
        }

        $devis = new Devis();
        $devis->setStatut('brouillon'); // Défaut : brouillon

        $form = $this->createForm(DevisType::class, $devis);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $devis->setStatut('en_attente'); // Mettez à jour le statut si nécessaire
                $devis->setEmail($currentUser->getEmail()); // Définir l'adresse email fournie par l'utilisateur
                $devis->setUser($currentUser);
                $entityManager->persist($devis);
                $entityManager->flush();

                // Envoi de l'email (vous devez ajouter le code pour l'envoi de l'email ici)

                $this->addFlash('success', 'Votre demande a bien été prise en compte, vérifiez votre adresse email');
                return $this->redirectToRoute('front_assistance');
            }
        }

        return $this->render('front/devis/new.html.twig', [
            'formDevis' => $form->createView(),
        ]);
    }
}
