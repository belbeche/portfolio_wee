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
    public function respondToTicket(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, $id): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        $currentMessage = $entityManager->getRepository(Message::class)->find($id);
        if (!$currentMessage) {
            throw $this->createNotFoundException('Message not found!');
        }

        // Corrige l'assignation du champ sender en utilisant l'entité User
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $currentMessage->getSender()->getEmail()]);

        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);

        $form->get('sender')->setData($currentUser);

        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $message = new Message();

            $message
                ->setDevis($currentMessage->getDevis())
                ->setSender($currentUser)
                ->setReceiver($currentMessage->getSender())
                ->setContent($ticket->getContent())
                ->setPriority($ticket->getPriority())
                ->setStatus('en_cours');

            $ticket
                ->setDevis($currentMessage->getDevis())
                ->setReceiver($currentMessage->getReceiver())
                ->setSender($currentUser)
                ->setStatus('en_cours')
                ->setPriority($ticket->getPriority());

            $entityManager->persist($message);
            $entityManager->persist($ticket);
            $entityManager->flush();

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
    public function show(Message $originalMessage, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $entityManager->getRepository(User::class)->find($originalMessage->getSender());

        $tickets = $entityManager->getRepository(Message::class)->findBy(
            ['sender' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('back/ticket/show.html.twig', [
            'tickets' => $tickets,
            'originalMessage' => $originalMessage,
        ]);
    }

    /**
     * @Route("back/supprimer/{id}/ticket", name="back_delete_ticket", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function remove(EntityManagerInterface $entityManager, Message $message, Request $request, Filesystem $filesystem): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $message->getId(), $request->request->get('_token'))) {
            $attachmentPath = $this->getParameter('uploads_directory') . '/' . $message->getAttachment();

            if ($filesystem->exists($attachmentPath)) {
                $filesystem->remove($attachmentPath);
            }

            $entityManager->remove($message);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_show_ticket', [
            'id' => $message->getDevis()->getId()
        ]);
    }
}
