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

        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $message = new Message();

            $message
                ->setDevis($currentMessage->getDevis())
                ->setSender($currentMessage->getSender())
                ->setReceiver($currentMessage->getReceiver())
                ->setContent($ticket->getContent())
                ->setPriority($ticket->getPriority())
                ->setStatus('en_cours')
                ->setTicket($ticket)
                ;

            $ticket
                ->setDevis($currentMessage->getDevis())
                ->setReceiver($currentUser)
                ->setSender($message->getSender())
                ->setStatus('en_cours')
                ->setPriority($ticket->getPriority())
                ->setContent($ticket->getContent())
                ->setCreatedAt(new \DateTime())
                ->setUpdatedAt(new \DateTime())
                ;


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
    public function remove(
        EntityManagerInterface $entityManager,
        Request $request,
        string $id
    ): RedirectResponse {
        $ticket = $entityManager->getRepository(Ticket::class)->find($id);

        // Valider le token CSRF
        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            // Supprimer le ticket
            try {
                $entityManager->remove($ticket);
                $entityManager->flush();

                $this->addFlash('success', 'Le ticket a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du ticket.');
            }
        } else {
            $this->addFlash('error', 'Échec de la validation du token CSRF. Ticket non supprimé.');
        }

        return $this->redirectToRoute('back_show_ticket', [
            'id' => $id
        ]);
    }
}
