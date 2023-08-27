<?php

namespace App\Controller\Front;

use App\Entity\Devis;
use App\Entity\User;
use App\Form\DevisType;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DevisController extends AbstractController
{
    /**
     * @Route("/devis-en-ligne", name="front_devis_new")
     * @throws TransportExceptionInterface
     */
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        /*if (!$currentUser) {
            // Utilisateur non connecté, renvoyer vers la page d'inscription
            return $this->redirectToRoute('app_register');

        } else {
            // Utilisateur connecté, utilisez son adresse email
            $email = $currentUser->getEmail();
        }*/

        // Vérifier si l'utilisateur a déjà un devis en fonction de l'adresse email
        /*$existingDevis = $entityManager->getRepository(Devis::class)->findOneBy(['email' => $email]);

        if ($existingDevis) {
            // Si un devis existe avec cette adresse email, rediriger vers la page d'assistance
            return $this->redirectToRoute('front_assistance');
        }*/


        $devis = new Devis();

        // Supposons que "type" est un paramètre GET de la requête
        $type = $request->query->get('type');

        $defaultValue = 'site_vitrine'; // valeur par défaut
        if ($type === 'cross_plateforme') {
            $defaultValue = 'application_cross_plateforme';
        }

        $form = $this->createForm(DevisType::class, $devis, [
            'default_type_de_site_web' => $defaultValue
        ]);

        $form->handleRequest($request);

        if ($request->isMethod('post')) {
            if ($form->isSubmitted() && $form->isValid()) {
                // Utilisation du Voter ici
                $this->denyAccessUnlessGranted('CREATE', $devis);
                $devis->setStatut('en_attente'); // Mettez à jour le statut si nécessaire
                /*$devis->setEmail($currentUser->getEmail()); // Définir l'adresse email fournie par l'utilisateur*/
                $entityManager->persist($devis);
                $entityManager->flush();

                $email = (new TemplatedEmail())
                    ->from('wbelbeche.s@gmail.com')
                    ->to($devis->getEmail())
                    ->subject('Récapitulatif de demande devis, Walid BELBECHE')
                    ->bcc('wbelbeche.s@gmail.com')
                    ->context([
                        'RegistredNumber' => $devis->getId(),
                        'email_address' => $devis->getEmail(),
                        'subject' => $devis->getTypeDeSiteWeb(),
                        'designWebsite' => $devis->getAttentesDesignWeb(),
                        'message' => $devis->getDescriptionProjet(),
                    ])
                    ->htmlTemplate('front/devis/email.html.twig');

                $mailer->send($email);

                $this->addFlash('success', 'Votre demande à bien était prise en compte, vérifiez votre adresse email');

                return $this->redirectToRoute('front_devis_set_password', [
                    'id' => $devis->getId()
                ]);
            }
        }

        return $this->render('front/devis/new.html.twig', [
            'formDevis' => $form->createView(),
        ]);
    }
    /**
     * @Route("/continuer/{id}", name="front_devis_set_password")
     */
    public function setPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {

        $devisUuid = $request->get('id'); // Récupérer l'UUID depuis la route

        // Vérifier si un devis existe avec cet UUID
        $existingDevis = $entityManager->getRepository(Devis::class)->findOneBy(['id' => $devisUuid]);

        if (!$existingDevis) {
            // Si l'UUID n'existe pas, redirigez vers une page d'erreur ou vers la création d'un devis
            return $this->redirectToRoute('front_devis_new');
        } else {
            return $this->redirectToRoute('app_login');
        }

        $email = $existingDevis->getEmail(); // Récupérer l'email associé à ce devis

        // Trouver ou créer l'entité User
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]) ?? new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);

        // Créer et traiter le formulaire pour le mot de passe
        $form = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('front_message_devis', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('front/devis/set_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/modifier-informations/{id}", name="front_devis_register")
     */
    public function editInfo(Request $request, EntityManagerInterface $entityManager,UserPasswordEncoderInterface $passwordEncoder,User $user)
    {
        $form = $this->createForm(UserType::class, $user);

        $form->remove('email');
        $form->remove('password');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login'); // redirige vers la page souhaitée
        }

        return $this->render('security/complete_info.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/devis/{id}/delete", name="front_devis_delete")
     * @IsGranted("ROLE_USER")
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, Devis $devis): Response
    {
        if ($request->isMethod('POST')) {
            $entityManager->remove($devis);
            $entityManager->flush();
        }

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
     * @Route("/devis/remerciement/{id}", name="front_message_devis")
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
