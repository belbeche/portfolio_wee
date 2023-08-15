<?php

namespace App\Controller\Back;

use App\Entity\Devis;
use App\Entity\Message;
use App\Entity\User;
use App\Entity\Ticket;
use App\Form\MessageType;
use App\Form\TicketType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends AbstractController
{
    /**
     * @Route("/back/ticket", name="back_ticket")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tickets = $entityManager->getRepository(Message::class)->findAll();

        return $this->render('back/ticket/index.html.twig', [
            'tickets' => $tickets
        ]);
    }
    /**
     * @Route("/back/ticket/nouveau/{id}", name="back_ticket_new")
     */
    public function new(Request $request, UserRepository $userRepository, MailerInterface $mailer,EntityManagerInterface $entityManager,$id): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Récupérer l'utilisateur actuellement connecté (l'expéditeur du message)
        $currentUser = $this->getUser();

        // Assurez-vous que l'utilisateur est connecté avant de continuer
        if (!$currentUser) {
            // Gérer l'absence d'utilisateur connecté (rediriger vers la page de connexion)
            return $this->redirectToRoute('app_login');
        }

        $email = $user->getEmail();

        // Récupérer les devis associés à l'email de l'utilisateur
        $devisList = $entityManager->getRepository(Devis::class)->findBy(['email' => $email]);

        // En utilisant les IDs de ces devis, récupérer les tickets/messages associés
        $devisIds = array_map(function($devis) {
            return $devis->getId();
        }, $devisList);

        $tickets = $entityManager->getRepository(Ticket::class)->findBy(['devis' => $devisIds]);

        dd($tickets);

        $responseMessage = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $responseMessage->setSender($currentUser);
        $responseMessage->setDevis($tickets);
        $responseMessage->setReceiverEmail($originalMessage->getSender()->getEmail());

        $form = $this->createForm(MessageType::class, $responseMessage, [
            'attr' => [
                'current_user' => $email,
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $responseMessage->setStatus('en_cours');
            $this->extracted($form, $responseMessage);
            $responseMessage->setDevis($originalMessage->getDevis());

            $entityManager->persist($responseMessage);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from('wbelbeche.s@gmail.com')
                ->to($originalMessage->getSender()->getEmail())
                ->subject('Réponse à votre demande, Devis , Walid BELBECHE')
                ->bcc('wbelbeche.s@gmail.com')
                ->context([
                    'email_address' => $currentUser->getEmail(),
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

        return $this->render('back/ticket/new.html.twig', [
            'form' => $form->createView(),
            'originalMessage' => $originalMessage,
        ]);
    }
    /**
     * @Route("/back/voir-ticket/{id}", name="back_show_ticket", methods={"GET"})
     */
    public function show(EntityManagerInterface $entityManager,Request $request, $id)
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        $email = $user->getEmail();

        // Récupérer les devis associés à l'email de l'utilisateur
        $devisList = $entityManager->getRepository(Devis::class)->findBy(['email' => $email]);

        // En utilisant les IDs de ces devis, récupérer les tickets/messages associés
        $devisIds = array_map(function($devis) {
            return $devis->getId();
        }, $devisList);

        // Maintenant, $tickets contient les tickets/messages associés aux devis de l'utilisateur

        // Récupérer les tickets envoyés par l'utilisateur et les ordonner par date de création décroissante
        /*$tickets = $entityManager->getRepository(Message::class)->findBy(
            ['sender' => $email],
            ['createdAt' => 'DESC']  // Ceci ordonne les tickets par date de création décroissante
        );*/

        $tickets = $entityManager->getRepository(Message::class)->findBy(['devis' => $devisIds]);

        // Maintenant, $tickets contient les tickets/messages associés aux devis de l'utilisateur

        foreach ($tickets as $ticket){
            dd($ticket);
        }

        return $this->render('back/ticket/show.html.twig', [
            'tickets' => $tickets
        ]);
    }

    /**
     * @Route("back/supprimer/{id}/ticket", name="back_delete_ticket", methods={"POST"})
     */
    public function remove(EntityManagerInterface $entityManager, Message $ticket, Request $request, Filesystem $filesystem): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $ticket->getId(), $request->request->get('_token'))) {

            // Récupérer le chemin du fichier associé au ticket
            $attachmentPath = $this->getParameter('uploads_directory') . '/' . $ticket->getAttachment();

            // Supprimer le fichier si il existe
            if ($filesystem->exists($attachmentPath)) {
                $filesystem->remove($attachmentPath);
            }

            $entityManager->remove($ticket);
            $entityManager->flush();
        }
        return $this->redirectToRoute('back_show_ticket', [
            'id' => $ticket->getDevis()
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param Message $responseMessage
     * @return void
     */
    public function extracted(\Symfony\Component\Form\FormInterface $form, Message $responseMessage): void
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
                echo 'une erreur s\'est produite lors du chargement du fichier';
            }

            $responseMessage->setAttachment($newFilename);
        }
    }
}