<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Devis;
use App\Entity\Message;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MessageController extends AbstractController
{
    /**
     * @Route("/profil/assistance", name="front_assistance")
     */
    public function home(EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        $devisList = $entityManager->getRepository(Devis::class)->findBy(['user' => $currentUser]);

        if (empty($devisList)) {
            return $this->redirectToRoute('front_devis_new');
        }

        return $this->render('front/ticket/home.html.twig', [
            'devisList' => $devisList,
        ]);
    }

    /**
     * @Route("/devis/nouveau-ticket/{id}", name="send_message", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
    public function sendMessage(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, $id): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer l'utilisateur (destinataire) à partir de l'ID fourni dans l'URL
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Créer un nouveau message
        $message = new Message();

        // Récupérer le devis associé à l'utilisateur
        $devis = $entityManager->getRepository(Devis::class)->findOneBy(['user' => $user]);

        if (!$devis) {
            $this->addFlash('warning', 'Aucun devis associé à cet utilisateur. Veuillez créer un devis.');
            return $this->redirectToRoute('front_devis_new');
        }

        $form = $this->createForm(MessageType::class, $message, [
            'current_user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $message
                ->setSender($currentUser)  // L'utilisateur connecté en tant qu'expéditeur
                ->setReceiver($form->get('receiver')->getData())  // Le destinataire est un email
                ->setDevis($devis)
                ->setStatus('en_attente');
        
            $entityManager->persist($message);
            $entityManager->flush();

            
        
            $email = (new TemplatedEmail())
                ->from($message->getReceiver())
                ->to($currentUser->getUserIdentifier())  // Email du service choisi
                ->subject('Demande assistance, ScriptZenIT')
                ->bcc('wbelbeche.s@gmail.com')
                ->context([
                    'email_address' => $message->getSender(),
                    'service' => $message->getReceiver(),
                    'subject' => $message->getDevis()->getTypeDeSiteWeb(),
                    'message' => $message->getContent(),
                    'status' => $message->getStatus(),
                ])
                ->htmlTemplate('front/ticket/email.html.twig');
        
            $mailer->send($email);
        
            $this->addFlash('info', 'Votre ticket a bien été créé, vérifiez votre adresse email.');
        
            return $this->redirectToRoute('front_show_ticket', [
                'id' => $message->getId()
            ]);
        }        

        return $this->render('front/ticket/create_message.html.twig', [
            'form' => $form->createView(),
            'devisList' => $devis,
        ]);
    }



    /**
    * @Route("/voir-ticket/{id}", name="front_show_ticket", methods={"GET"})
    */
    public function show(EntityManagerInterface $entityManager, $id): Response
    {
        $currentUser = $this->getUser();

        // Vérifie que l'utilisateur est connecté
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        // Récupère le devis en fonction de l'ID et de l'utilisateur connecté
        $devis = $entityManager->getRepository(Devis::class)->findOneBy([
            'id' => $id,
            'user' => $currentUser
        ]);

        // Si le devis n'existe pas ou n'appartient pas à l'utilisateur, rediriger
        if (!$devis) {
            $this->addFlash('error', 'Devis non trouvé ou vous n\'avez pas accès à ce devis.');
            return $this->redirectToRoute('front_assistance');
        }

        // Récupère les tickets associés à ce devis
        $tickets = $entityManager->getRepository(Message::class)->findBy(
            ['devis' => $devis],
            ['createdAt' => 'DESC']
        );

        // Rend la vue avec les tickets et le devis
        return $this->render('front/ticket/index.html.twig', [
            'tickets' => $tickets,
            'devisList' => $devis,
        ]);
    }

    /**
     * @Route("/repondre-ticket/{id}", name="respond_to_ticket", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
    public function respondToTicket(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, Message $originalMessage): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        $responseMessage = new Message();
        $responseMessage->setSender($currentUser);
        $responseMessage->setReceiver($originalMessage->getSender());

        $form = $this->createForm(MessageType::class, $responseMessage, [
            'current_user' => $this->getUser(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $responseMessage->setStatus('en_attente');
            $responseMessage->setDevis($originalMessage->getDevis());

            $this->extracted($form, $responseMessage);

            $entityManager->persist($responseMessage);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from($currentUser->getUserIdentifier())
                ->to($originalMessage->getSender()->getEmail())
                ->subject('Réponse à votre demande, Devis, ScriptZenIT')
                ->bcc('wbelbeche.s@gmail.com')
                ->context([
                    'email_address' => $currentUser->getUserIdentifier(),
                    'priority' => $responseMessage->getPriority(),
                    'message' => $responseMessage->getContent(),
                    'status' => $responseMessage->getStatus(),
                ])
                ->htmlTemplate('front/ticket/response_email.html.twig');

            $mailer->send($email);

            $this->addFlash('info', 'Votre réponse a bien été envoyée.');

            return $this->redirectToRoute('front_show_ticket', [
                'id' => $originalMessage->getId()
            ]);
        }

        return $this->render('front/ticket/respond_to_ticket.html.twig', [
            'form' => $form->createView(),
            'originalMessage' => $originalMessage,
        ]);
    }

    /**
     * @Route("/ticket/supprimer/{id}", name="front_delete_ticket", methods={"POST"})
     * @IsGranted("ROLE_USER")
     */
    public function remove(EntityManagerInterface $entityManager, Message $ticket, Request $request, Filesystem $filesystem): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $ticket->getId(), $request->request->get('_token'))) {
            $attachmentPath = $this->getParameter('uploads_directory') . '/' . $ticket->getAttachment();

            if ($filesystem->exists($attachmentPath)) {
                $filesystem->remove($attachmentPath);
            }

            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('front_show_ticket', [
            'id' => $ticket->getDevis()->getId()
        ]);
    }

    private function extracted(\Symfony\Component\Form\FormInterface $form, Message $responseMessage): void
    {
        $attachmentFile = $form->get('attachment')->getData();

        if ($attachmentFile) {
            $originalFilename = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $attachmentFile->guessExtension();

            try {
                $attachmentFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // Log the error or handle it accordingly
                echo 'Une erreur s\'est produite lors du chargement du fichier';
            }

            $responseMessage->setAttachment($newFilename);
        }
    }
}
