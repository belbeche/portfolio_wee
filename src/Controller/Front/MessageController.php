<?php

namespace App\Controller;

ini_set('memory_limit', '1024M');

use App\Entity\User;
use App\Entity\Devis;
use App\Entity\Message;
use App\Form\DevisType;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{

    /**
     * @Route("/assistance", name="front_assistance")
     */
    public function home(EntityManagerInterface $entityManager): Response
    {

        $currentUser = $this->getUser();

        // Récupérer l'utilisateur actuellement connecté (l'expéditeur du message)
        $email = $currentUser->getEmail();

        // Assurez-vous que l'utilisateur est connecté avant de continuer
        if ($currentUser === null) {
            // Gérer l'absence d'utilisateur connecté (rediriger vers une page de connexion par exemple)
            return $this->redirectToRoute('app_login'); // Remplacez "login" par le nom de votre route de connexion
        }

        // Récupérer le devis associé à l'utilisateur actuellement connecté
        $devis = $entityManager->getRepository(Devis::class)->findBy(['email' => $email]);

        // Vérifier si le devis a été trouvé
        if (!$devis) {
            // Gérer le cas où aucun devis n'a été trouvé pour l'utilisateur actuel
            // Rediriger vers une page d'erreur ou afficher un message approprié
            return $this->redirectToRoute('front_devis_new'); // Remplacez "front_devis_new" par le nom de votre route pour créer un nouveau devis
        }

        return $this->render('front/ticket/home.html.twig', [
            'devisUser' => $devis,
        ]);
    }


    /**
     * @Route("/nouveau-ticket", name="send_message", methods={"GET","POST"})
     */
    public function sendMessage(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur actuellement connecté (l'expéditeur du message)
        $currentUser = $this->getUser();

        // Assurez-vous que l'utilisateur est connecté avant de continuer
        if (!$currentUser) {
            // Gérer l'absence d'utilisateur connecté (rediriger vers la page de connexion)
            return $this->redirectToRoute('app_login');
        }

        $email = $currentUser->getEmail();

        // L'utilisateur a déjà des devis, nous afficherons le formulaire pour remplir le message avec la sélection de devis
        $message = new Message();
        $message->setSender($currentUser);

        // Récupérer les devis associés à l'email de l'utilisateur
        $devisList = $entityManager->getRepository(Devis::class)->findBy(['email' => $email]);

        // Traitez $devisList comme vous le souhaitez, par exemple, transmettez-le au formulaire
        // pour afficher les devis associés dans le champ de sélection.

        $form = $this->createForm(MessageType::class, $message, [
            'attr' => [
                'current_user' => $email, // Assurez-vous que l'email est correctement défini ici
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si l'utilisateur a sélectionné un devis existant ou s'il veut en créer un nouveau
            $devisChoice = $form->get('devis')->getData();

            if ($devisChoice === 'new') {
                // L'utilisateur veut créer un nouveau devis
                return $this->redirectToRoute('front_devis_new'); // Rediriger vers la page de création de devis
            } else {
                // L'utilisateur a choisi un devis existant
                $devis = $devisChoice;
            }

            // Associer le devis au message
            $message->setDevis($devis);

            // Définition du statut du ticket
            $message->setStatus('en_attente');

            // Persistez l'objet Message dans la base de données
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('front_show_ticket', [
                'id' => $message->getId()
            ]);
        }

        return $this->render('front/ticket/create_message.html.twig', [
            'form' => $form->createView(),
            'devisList' => $devisList, // Transmettez la liste des devis au template
        ]);
    }


    /**
     * @Route("/voir-ticket/{id}", name="front_show_ticket", methods={"GET"})
     */
    public function show(EntityManagerInterface $entityManager, $id)
    {
        // Récupérer les tickets envoyés par l'utilisateur
        $tickets = $entityManager->getRepository(Message::class)->findBy(['sender' => $this->getUser()]);

        return $this->render('front/ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }
}
