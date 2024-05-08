<?php

namespace App\Controller\Front;

use App\Entity\Subject;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\Devis;
use App\Entity\Message;
use App\Form\DevisType;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MessageController extends AbstractController
{

    /**
     * @Route("/profil/assistance", name="front_assistance")
     */
    public function home(EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();

        // Récupérer l'utilisateur actuellement connecté (l'expéditeur du message)
        $email = $currentUser->getUserIdentifier();

        // Récupérer tous les devis associés à l'utilisateur actuellement connecté
        $devisList = $entityManager->getRepository(Devis::class)->findBy(['email' => $email]);

        // Vérifier s'il y a des devis trouvés
        if (empty($devisList)) {
            // Rediriger vers une page d'erreur ou afficher un message approprié
            return $this->redirectToRoute('front_devis_new');
        }

        return $this->render('front/ticket/home.html.twig', [
            'devisList' => $devisList
        ]);
    }

    /**
     * @Route("/devis/nouveau-ticket/{id}", name="send_message", methods={"GET","POST"})
     * @IsGranted("ROLE_USER")
     * @throws LogicException|\Symfony\Component\Mime\Exception\LogicException
     */
    public function sendMessage(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer,$id): Response
    {
        // Récupérer l'utilisateur à partir de son ID
        $user = $entityManager->getRepository(User::class)->find($id);

        // L'utilisateur a déjà des devis, nous afficherons le formulaire pour remplir le ticket avec la sélection de devis
        $message = new Message();

        // Récupérer les devis associés à l'email de l'utilisateur
        $devisList = $entityManager->getRepository(Devis::class)->findOneBy(['user' => $user]);

        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($devisList === 'new') {
                // L'utilisateur veut créer un nouveau devis
                return $this->redirectToRoute('front_devis_new'); // Rediriger vers la page de création de devis
            } else {
                // L'utilisateur a choisi un devis existant
                $devis = $devisList;
            }

            $message
                // Associer le devis au message
                ->setDevis($devis)
            ;

            // Définition du statut du ticket
            $message->setStatus('en_attente');
            $message->setReceiver($user);

            $this->extracted($form, $message);

            // Persistez l'objet Message dans la base de données
            $entityManager->persist($message);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from($message->getSender())
                ->to($devis->getEmail())
                ->subject('Demande assistance , ScriptZenIT')
                ->bcc('wbelbeche.s@gmail.com')
                ->context([
                    'email_address' => $user->getEmail(),
                    'service' => $message->getReceiver(),
                    'subject' => $message->getDevis(),
                    'message' => $message->getContent(),
                    'status' => $message->getStatus(),
                ])
                ->htmlTemplate('front/ticket/email.html.twig');

            $mailer->send($email);

            $this->addFlash('info', 'Votre ticket à bien été crée, vérifiez votre adresse email');

            return $this->redirectToRoute('front_show_ticket', [
                'id' => $message->getId()
            ]);
        }

        return $this->render('front/ticket/create_message.html.twig',[
                'form' => $form->createView(),
                'devisList' => $devisList, // Transmettez la liste des devis au template
            ]
        );
    }

    /**
     * @Route("/voir-ticket/{id}", name="front_show_ticket", methods={"GET"})
     */
    public function show(EntityManagerInterface $entityManager, $id,Request $request)
    {
        // Récupérer l'utilisateur actuellement connecté (l'expéditeur du message)
        $currentUser = $this->getUser();

        // Récupérer l'utilisateur actuellement connecté (l'expéditeur du message)
        $email = $currentUser->getUserIdentifier();

        // Récupérer les tickets envoyés par l'utilisateur et les ordonner par date de création décroissante
        $tickets = $entityManager->getRepository(Message::class)->findBy(
            ['receiver' => $this->getUser()],
            ['createdAt' => 'DESC']  // Ceci ordonne les tickets par date de création décroissante
        );

        // Récupérer le devis associé à l'utilisateur actuellement connecté
        $devis = $entityManager->getRepository(Devis::class)->findBy(['email' => $email]);

        return $this->render('front/ticket/index.html.twig', [
            'tickets' => $tickets,
            'devis' => $devis
        ]);
    }

    /**
     * @Route("/repondre-ticket/{id}", name="respond_to_ticket", methods={"GET","POST"})
     * @throws TransportExceptionInterface
     */
    public function respondToTicket(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, Message $originalMessage): Response
    {
        // Récupérer l'utilisateur actuellement connecté (l'expéditeur du message)
        $currentUser = $this->getUser();

        // Assurez-vous que l'utilisateur est connecté avant de continuer
        if (!$currentUser) {
            // Gérer l'absence d'utilisateur connecté (rediriger vers la page de connexion)
            return $this->redirectToRoute('app_login');
        }

        $email = $currentUser->getEmail();

        $responseMessage = new Message();
        $responseMessage->setSender($originalMessage->getSender());
        $responseMessage->setReceiver($this->getUser());

        $form = $this->createForm(MessageType::class, $responseMessage, [
            'attr' => [
                'current_user' => $email,
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $responseMessage->setStatus('en_attente');
            $this->extracted($form, $responseMessage);
            $responseMessage->setDevis($originalMessage->getDevis());

            $entityManager->persist($responseMessage);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from($originalMessage->getSender())
                ->to($currentUser->getUserIdentifier())
                ->subject('Réponse à votre demande, Devis , ScriptZenIT')
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
     */
    public function remove(EntityManagerInterface $entityManager, Message $ticket, Request $request, Filesystem $filesystem): RedirectResponse
    {
            // Récupérer le chemin du fichier associé au ticket
            $attachmentPath = $this->getParameter('uploads_directory');

            // Supprimer le fichier si il existe
            if ($filesystem->exists($attachmentPath)) {
                $filesystem->remove($attachmentPath);
            }

            $entityManager->remove($ticket);
            $entityManager->flush();

        return $this->redirectToRoute('front_show_ticket', [
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
