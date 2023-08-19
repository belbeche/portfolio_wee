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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends AbstractController
{
    /**
     * @Route("/back/ticket", name="back_ticket")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tickets = $entityManager->getRepository(Message::class)->findAll();

        return $this->render('back/ticket/index.html.twig', [
            'tickets' => $tickets
        ]);
    }
    /**
     * @Route("/back/repondre-ticket/{id}", name="back_respond_to_ticket", methods={"GET","POST"})
     * @throws TransportExceptionInterface
     * @IsGranted("ROLE_ADMIN")
     */
    public function respondToTicket(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer,$id): Response
    {
        // Récupérer l'utilisateur actuellement connecté (l'expéditeur du message)
        $currentUser = $this->getUser();

        // Assurez-vous que l'utilisateur est connecté avant de continuer
        if (!$currentUser) {
            // Gérer l'absence d'utilisateur connecté (rediriger vers la page de connexion)
            return $this->redirectToRoute('app_login');
        }

        $currentMessage = $entityManager->getRepository(Message::class)->find($id);
        if (!$currentMessage) {
            die('Message not found!');
        }

        $user = $entityManager->getRepository(User::class)->findBy(['email' => $currentMessage->getSender()]);

        $ticket = new Ticket();

        $form = $this->createForm(TicketType::class, $ticket);

        /*$form->get('sender')->setData($currentMessage->getSender()->getEmail());*/
        /*$form->get('sender')->setData($currentMessage->getSender());*/

        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted()) {

            /*$senderId = $form->get('sender')->getData();

            $sender = $entityManager->getRepository(User::class)->find($senderId);*/

            $message = new Message();

            $message
                ->setDevis($currentMessage->getDevis())
                ->setSender($currentMessage->getSender())
                ->setReceiver($currentMessage->getReceiver())
                ->setContent($ticket->getContent())
                ->setPriority($ticket->getPriority())
                ->setStatus('en_cours')
            ;

            $ticket
                ->setDevis($currentMessage->getDevis())
                ->setReceiver($currentMessage->getReceiver())
                ->setSender($message->getSender()) // Si le ticket est envoyé par l'utilisateur actuellement connecté.
                ->setStatus('en_cours')
                ->setPriority($ticket->getPriority());
            ;

            /*dump($message);dd($ticket);*/
            $entityManager->persist($message);
            $entityManager->persist($ticket);
            $entityManager->flush();

            /*$email = (new TemplatedEmail())
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

            $mailer->send($email);*/

            $this->addFlash('info', 'Votre réponse a bien été envoyée.');

            return $this->redirectToRoute('back_show_ticket', [
                'id' => $currentMessage->getId()
            ]);
        }
        return $this->render('back/ticket/new.html.twig', [
            'form' => $form->createView(),
            'originalMessage' => $currentMessage,
        ]);
    }
    /**
     * @Route("/back/voir-ticket/{id}", name="back_show_ticket", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Message $originalMessage,EntityManagerInterface $entityManager,Request $request)
    {

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $originalMessage->getSender()->getEmail()]);

        // Récupérer les tickets envoyés par l'utilisateur et les ordonner par date de création décroissante

        $tickets = $entityManager->getRepository(Message::class)->findBy(
            ['sender' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('back/ticket/show.html.twig', [
            'tickets' => $tickets
        ]);
    }

    /**
     * @Route("back/supprimer/{id}/ticket", name="back_delete_ticket", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
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
}
