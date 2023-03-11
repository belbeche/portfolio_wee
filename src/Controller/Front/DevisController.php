<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Devis;
use App\Form\DevisType;
use App\Repository\DevisRepository;
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
     * @Route("/devis-en-ligne/{id}", name="front_devis_new")
     * @throws TransportExceptionInterface
     */
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $devis = new Devis();

        $form = $this->createForm(DevisType::class, $devis);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $request->get('id')]);
                $devis->setUser($user);
                $entityManager->persist($devis);
                $entityManager->flush($devis);

                $email = (new TemplatedEmail())
                    ->from('wbelbeche.s@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Récapitulatif de devis, Portfolio - Wee')
                    ->bcc('wbelbeche.s@gmail.com')
                    ->context([
                        'name' => $user->getNom(),
                        'registrationNumber' => $user->getId(),
                        'subject' => $devis->getTypeDeSiteWeb()
                    ])
                    ->htmlTemplate('front/devis/email.html.twig');

                $mailer->send($email);

                return $this->redirectToRoute('front_devis_utilisateur_show');
            }
        }

        return $this->render('front/devis/new.html.twig', [
            'formDevis' => $form->createView(),
        ]);
    }

    /**
     * @Route("/devis/utilisateur", name="front_devis_utilisateur_show")
     * 
     * @param DevisRepository $devisRepo
     * @return Response
     */
    public function Show(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $entityManager->getRepository(Devis::class)->findBy(['user' => $this->getUser()]);

        return $this->render('front/devis/show.html.twig', [
            'allQuote' => $user,
        ]);
    }
}
