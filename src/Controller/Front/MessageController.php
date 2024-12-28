<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Devis;
use App\Entity\Ticket;
use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\DevisRepository;
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
    * @Route("/aide/nouveau-ticket", name="send_message", methods={"GET", "POST"})
    * @IsGranted("ROLE_USER")
    */
    public function sendMessage(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        DevisRepository $devisRepository
    ): Response {
        // Récupérer l'utilisateur actuellement connecté
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        if (!$user instanceof User) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Récupérer les devis de l'utilisateur connecté
        $userDevis = $devisRepository->findBy(['user' => $user]);

        if (!$userDevis) {
            $this->addFlash('warning', 'Aucun devis associé à cet utilisateur. Veuillez créer un devis.');
            return $this->redirectToRoute('front_devis_new');
        }

        // Créer un nouveau message
        $message = new Message();

        // Créer le formulaire
        $form = $this->createForm(MessageType::class, $message, [
            'current_user' => $user,  // L'utilisateur connecté
            'devisList' => $userDevis, // Liste des devis récupérés pour l'utilisateur
        ]);               
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $receiverEmail = $form->get('receiver')->getData();
            $receiver = $entityManager->getRepository(User::class)->findOneBy(['email' => $receiverEmail]);

            if (!$receiver instanceof User) {
                $this->addFlash('error', 'Destinataire invalide.');
                return $this->redirectToRoute('send_message');
            }

            // Créer un nouveau ticket
            $ticket = new Ticket();
            $ticket
                ->setSender($user)
                ->setReceiver($receiver)
                ->setStatus($form->get('status')->getData())
                ->setDevis($form->get('devis')->getData())
                ->setContent($form->get('content')->getData())
                ->setPriority($form->get('priority')->getData());

            $entityManager->persist($ticket);

            // Associer le ticket au message
            $message
                ->setSender($user)
                ->setReceiver($receiverEmail)
                ->setDevis($form->get('devis')->getData())
                ->setContent($form->get('content')->getData())
                ->setTicket($ticket)
                ->setStatus($form->get('status')->getData());

            $entityManager->persist($message);
            $entityManager->flush();

            // Envoi de l'email
            $email = (new TemplatedEmail())
                ->from('contact@scriptzenit.fr')
                ->to($receiverEmail)
                ->bcc('wbelbeche.s@gmail.com')
                ->subject('Demande assistance - ScriptZenIT')
                ->context([
                    'email_address' => $user->getEmail(),
                    'service' => $receiverEmail,
                    'subject' => $form->get('devis')->getData()->getTypeDeSiteWeb(),
                    'message' => $form->get('content')->getData(),
                    'status' => $form->get('status')->getData(),
                ])
                ->htmlTemplate('front/ticket/email.html.twig');

            $mailer->send($email);

            $this->addFlash('success', 'Votre ticket a été créé. Vérifiez votre email pour plus d\'informations.');

            return $this->redirectToRoute('front_ticket_details', [
                'id' => $ticket->getId(),
            ]);
        }

        return $this->render('front/ticket/create_message.html.twig', [
            'form' => $form->createView(),
            'devisList' => $userDevis,
        ]);
    }

    /**
    * @Route("/voir-tickets/aide", name="front_show_ticket", methods={"GET"})
    * @IsGranted("ROLE_USER")
    */
    public function showAllTickets(EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();

        // Vérifie que l'utilisateur est connecté
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer l'utilisateur actuellement connecté
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        if (!$user instanceof User) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Récupérer les devis de l'utilisateur connecté
        $userDevis = $entityManager->getRepository(Devis::class)->findBy(['user' => $currentUser]);

        // Si le devis n'existe pas ou n'appartient pas à l'utilisateur, rediriger
        if (!$userDevis) {
            $this->addFlash('error', 'Devis non trouvé ou vous n\'avez pas accès à ce devis.');
            return $this->redirectToRoute('front_assistance');
        }

        // Récupère les tickets associés à ce devis
        $tickets = $entityManager->getRepository(Message::class)->findBy(['devis' => $userDevis[0]]);

        // Rend la vue avec les tickets et le devis
        return $this->render('front/ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * @Route("/ticket/details/{id}", name="front_ticket_details", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
    public function ticketDetails(
        Request $request,
        EntityManagerInterface $entityManager,
        string $id,
        DevisRepository $devisRepository
    ): Response {
        $currentUser = $this->getUser();

        // Vérifie que l'utilisateur est connecté
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer l'utilisateur actuellement connecté
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        if (!$user instanceof User) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Récupérer le ticket
        $ticket = $entityManager->getRepository(Ticket::class)->find($id);

        // Vérifier que le ticket existe et appartient à l'utilisateur
        if (!$ticket || $ticket->getSender() !== $currentUser) {
            $this->addFlash('error', 'Ticket non trouvé ou vous n\'avez pas accès à ce ticket.');
            return $this->redirectToRoute('front_show_ticket');
        }

        // Récupérer les devis de l'utilisateur connecté
        $userDevis = $devisRepository->findBy(['user' => $user]);

        if (!$userDevis) {
            $this->addFlash('warning', 'Aucun devis associé à cet utilisateur. Veuillez créer un devis.');
            return $this->redirectToRoute('front_devis_new');
        }

        // Préparer le formulaire de réponse
        $responseMessage = new Message();
        $responseMessage->setTicket($ticket);
        $responseMessage->setSender($currentUser); // Associe l'utilisateur connecté
        $responseMessage->setCreatedAt(new \DateTime());

        $responseForm = $this->createForm(MessageType::class, $responseMessage, [
            'current_user' => $currentUser,
            'devisList' => $userDevis, // Liste des devis récupérés pour l'utilisateur
        ]);

        $responseForm->handleRequest($request);

        if ($responseForm->isSubmitted() && $responseForm->isValid()) {
            $entityManager->persist($responseMessage);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réponse a été envoyée avec succès.');

            return $this->redirectToRoute('front_ticket_details', [
                'id' => $ticket->getId(),
            ]);
        }

        return $this->render('front/ticket/details.html.twig', [
            'ticket' => $ticket,
            'responseForm' => $responseForm->createView(),
        ]);
    }

    /**
    * @Route("/repondre-ticket/{id}", name="respond_to_ticket", methods={"GET", "POST"})
    * @IsGranted("ROLE_USER")
    */
    public function respondToTicket(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        Message $originalMessage,
        DevisRepository $devisRepository,
    ): Response {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que le message d'origine a un ticket associé
        $ticket = $originalMessage->getTicket();
        if (!$ticket) {
            $this->addFlash('error', 'Le ticket associé au message original est introuvable.');
            return $this->redirectToRoute('front_show_ticket');
        }

        // Récupérer l'utilisateur actuellement connecté
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        if (!$user instanceof User) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Récupérer les devis de l'utilisateur connecté
        $userDevis = $devisRepository->findBy(['user' => $user]);

        if (!$userDevis) {
            $this->addFlash('warning', 'Aucun devis associé à cet utilisateur. Veuillez créer un devis.');
            return $this->redirectToRoute('front_devis_new');
        }

        // Créer un nouveau message pour la réponse
        $responseMessage = new Message();
        $responseMessage
            ->setSender($currentUser)
            ->setReceiver($originalMessage->getSender())
            ->setStatus('en_attente')
            ->setDevis($originalMessage->getDevis())
            ->setTicket($ticket); // Associer le ticket

        // Créer le formulaire
        $form = $this->createForm(MessageType::class, $responseMessage, [
            'current_user' => $currentUser,
            'devisList' => $userDevis, // Liste des devis récupérés pour l'utilisateur
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer le message de réponse
            $entityManager->persist($responseMessage);
            $entityManager->flush();

            // Envoi de l'email
            $email = (new TemplatedEmail())
                ->from('contact@scriptzenit.fr')
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
                'id' => $ticket->getId(),
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
    public function remove(
        EntityManagerInterface $entityManager,
        Request $request,
        string $id,
        Message $originalMessage
    ): RedirectResponse {
        $currentUser = $this->getUser();

        // Vérifie que l'utilisateur est connecté
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que le message d'origine a un ticket associé
        $ticket = $originalMessage->getTicket();
        if (!$ticket) {
            $this->addFlash('error', 'Le ticket associé au message original est introuvable.');
            return $this->redirectToRoute('front_show_ticket');
        }

        // Valide le token CSRF
        if (!$this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Échec de la validation du token CSRF. Ticket non supprimé.');
            return $this->redirectToRoute('front_show_ticket', [
                'id' => $ticket->getId(),
            ]);
        }

        // Supprime les messages associés au ticket
        foreach ($ticket->getMessages() as $message) {
            $entityManager->remove($message);
        }

        // Supprime le ticket
        $entityManager->remove($ticket);
        $entityManager->flush();

        $this->addFlash('success', 'Le ticket et ses messages ont été supprimés avec succès.');

        return $this->redirectToRoute('front_show_ticket');
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
