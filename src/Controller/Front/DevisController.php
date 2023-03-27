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
     * @Route("/devis-en-ligne", name="front_devis_new")
     * @throws TransportExceptionInterface
     */
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $devis = new Devis();

        $form = $this->createForm(DevisType::class, $devis);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {


                $devis->setUser(null);
                $entityManager->persist($devis);
                $entityManager->flush();

                $email = (new TemplatedEmail())
                    ->from('wbelbeche.s@gmail.com')
                    ->to($devis->getEmail())
                    ->subject('Récapitulatif de devis, Portfolio - Wee')
                    ->bcc('wbelbeche.s@gmail.com')
                    ->context([
                        'name' => $devis->getUser(),
                        'registrationNumber' => $devis->getId(),
                        'subject' => $devis->getTypeDeSiteWeb()
                    ])
                    ->htmlTemplate('front/devis/email.html.twig');

                $mailer->send($email);
                    $this->addFlash('success', 'Votre demande à bien était prise en compte, vérifiez votre adresse email');
                    return $this->redirectToRoute('front_home');
                }
        }

        return $this->render('front/devis/new.html.twig', [
            'formDevis' => $form->createView(),
        ]);
    }
}
