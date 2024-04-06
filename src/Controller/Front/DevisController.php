<?php

namespace App\Controller\Front;

use App\Entity\Devis;
use App\Entity\User;
use App\Form\DevisType;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class DevisController extends AbstractController
{
    /**
     * @Route("/devis-en-ligne", name="front_devis_new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, Security $security): Response
    {

        $devis = new Devis();

        // Créer le formulaire de devis en utilisant l'e-mail récupéré ou laisser l'e-mail vide
        $form = $this->createForm(DevisType::class, $devis);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Utilisation du Voter ici
            $this->denyAccessUnlessGranted('CREATE', $devis);

            $devis->setStatut('en_attente'); // Mettez à jour le statut si nécessaire
            $devis->setPrix('0'); // prix à 0 au moment de la création

            if($this->getUser()){
                // Récupérer l'utilisateur actuellement authentifié s'il existe
                $user = $security->getUser();

                // Vérifier si l'utilisateur est authentifié et s'il a un e-mail
                if ($user instanceof UserInterface && $user->getUserIdentifier()) {
                    // Utilisateur déjà inscrit, utiliser son e-mail
                    $devis->setEmail($user->getUserIdentifier());
                }
            }

            // Envoi de l'e-mail avec le récapitulatif du devis
            $email = (new TemplatedEmail())
                ->from(new Address('contact@scriptzenit.fr', 'L\'équipe Scriptzenit'))
                ->to($devis->getEmail())
                ->subject('Récapitulatif de demande de devis')
                ->context([
                    'registredNumber' => $devis->getId(),
                    'emailAddress' => $devis->getEmail(),
                    'subject' => $devis->getTypeDeSiteWeb(),
                    'designWebsite' => $devis->getAttentesDesignWeb(),
                    'typeWeb' => $devis->getTypeDeSiteWeb(),
                    'message' => $devis->getDescriptionProjet()
                ])
                ->htmlTemplate('front/devis/email.html.twig');
                $mailer->send($email);

            $entityManager->persist($devis);
            $entityManager->flush();


            $this->addFlash('success', 'Votre demande a bien été prise en compte, vérifiez votre adresse e-mail.');
            $this->redirectToRoute('front_confirmation_estimate');
        }

        return $this->render('front/devis/new.html.twig', [
            'formDevis' => $form->createView(),
        ]);
    }

    /**
     * @Route("/devis/{id}/delete", name="front_devis_delete")
     * @IsGranted("ROLE_USER")
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, Devis $devis): Response
    {

        $entityManager->remove($devis);
        $entityManager->flush();

        return $this->redirectToRoute('front_assistance');
    }

    /**
     * @Route("/telecharger-devis/{id}", name="front_download_devis")
     * @IsGranted("ROLE_USER")
     */
    public function downloadDevis(Pdf $snappy, EntityManagerInterface $entityManager, $id): Response
    {
        $currentUser = $this->getUser();

        $devis = $entityManager->getRepository(Devis::class)->find($id);

        $html = $this->renderView('front/devis/show.html.twig', [
            'devis' => $devis
        ]);

        $filename = 'devis_' . $currentUser->getId() . '.pdf';

        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]
        );
    }

    /**
     * @Route("/devis/remerciement/{id}", name="front_confirmation_estimate")
     */
    public function messageConfirmation(
        EntityManagerInterface $entityManager,
        Request $request,
        User $user
    ): Response
    {
        $user = $entityManager->getRepository(User::class)->find($user);

        if ($user === null) {
            // Gérer l'erreur, peut-être rediriger vers une page d'erreur
            throw $this->createNotFoundException('Aucun utilisateur avec cette adresse e-mail n\'a été trouvé.');
        }

        return $this->render('front/devis/confirmation.html.twig', [
            'user' => $user,
        ]);
    }
}
