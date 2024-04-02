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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DevisController extends AbstractController
{
    /**
     * @Route("/devis-en-ligne", name="front_devis_new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $devis = new Devis();
        $user = $entityManager->getRepository(User::class)->find($this->getUser());
        /*if ($form->isSubmitted() && $form->isValid()) {
            // Utilisation du Voter ici
            $this->denyAccessUnlessGranted('CREATE', $devis);
            $devis->setStatut('en_attente'); // Mettez à jour le statut si nécessaire
            $devis->setPrix('0'); // prix à 0 au moment de la création

            // Génération du code temporaire
            $tempCode = bin2hex(random_bytes(6)); // Génère un code hexadécimal de 6 caractères

            // Vérifier si l'adresse e-mail est vide
            if (!empty($email)) {


                dd($devisUuid);
                // Vérifier si un devis existe avec cette adresse e-mail

                $email->setPassword($tempCode);
            }

            // Si l'adresse e-mail est vide ou si aucun devis n'a été trouvé, effectuer d'autres actions ici
            /*$entityManager->persist($devis);
            $entityManager->flush();

            
        }*/
            // Supposons que "type" est un paramètre GET de la requête
            $type = $request->query->get('type');

            $defaultValue = 'site_vitrine'; // valeur par défaut
            if ($type === 'cross_plateforme') {
                $defaultValue = 'application_cross_plateforme';
            } elseif ($type === 'site_real_estate') {
                $defaultValue = 'site_real_estate';
            } elseif ($type === 'site_e-commerce') {
                $defaultValue = 'site_e-commerce';
            } elseif ($type === 'site_portfolio') {
                $defaultValue = 'site_portfolio';
            }

            $form = $this->createForm(DevisType::class, $devis, [
                'default_type_de_site_web' => $defaultValue
            ]);

            $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Utilisation du Voter ici
            $this->denyAccessUnlessGranted('CREATE', $devis);

            if($user){
                $devis->setEmail($user->getEmail());
            }
            $devis->setStatut('en_attente'); // Mettez à jour le statut si nécessaire
            $devis->setPrix('0'); // prix à 0 au moment de la création

            // Génération du code temporaire
            // $tempCode = bin2hex(random_bytes(6)); // Génère un code hexadécimal de 6 caractères

            // // Vérifier si l'adresse e-mail est vide
            // if (!empty($email)) {


            //     dd($devisUuid);
            //     // Vérifier si un devis existe avec cette adresse e-mail

            //     $email->setPassword($tempCode);
            // }

            // Envoi de l'email avec le code temporaire
            $email = (new TemplatedEmail())
                ->from(new Address('support@scriptzenit.fr', 'L\'equipe Scriptzenit'))
                ->to($devis->getEmail())
                ->subject('Récapitulatif de demande de devis - Walid BELBECHE')
                ->context([
                    'registredNumber' => $devis->getId(),
                    'emailAddress' => $devis->getEmail(),
                    'subject' => $devis->getTypeDeSiteWeb(),
                    'designWebsite' => $devis->getAttentesDesignWeb(),
                    'message' => $devis->getDescriptionProjet()
                    // 'temporaryCode' => $tempCode,
                ])
                ->htmlTemplate('front/devis/email.html.twig');

            try {
                $entityManager->persist($devis);
                $entityManager->flush();
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi de l\'e-mail');
                return $this->redirectToRoute('front_devis_new');
            }

            $this->addFlash('success', 'Votre demande a bien été prise en compte, vérifiez votre adresse e-mail.');

            // Rediriger vers la page de confirmation
            return $this->redirectToRoute('app_login');
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

        // Vérifier si l'UUID est valide
        if (!Uuid::isValid($devisUuid)) {
            throw $this->createNotFoundException('Devis non trouvé');
        }

        // Récupérer le devis correspondant à l'UUID
        $devis = $entityManager->getRepository(Devis::class)->findOneBy(['id' => $devisUuid]);

        // Vérifier si le devis existe
        if (!$devis) {
            throw $this->createNotFoundException('Devis non trouvé');
        }

        $email = $devis->getEmail(); // Récupérer l'email associé à ce devis

        // Vérifier si un utilisateur existe avec cet email
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            // Si aucun utilisateur n'existe avec cet email, c'est un nouvel utilisateur, affichez le formulaire de création de mot de passe
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
        } else {
            // Si un utilisateur existe avec cet email, redirigez-le vers la page de message du devis
            return $this->redirectToRoute('front_assistance', ['id' => $user->getId()]);
        }

        // Créer et traiter le formulaire pour le mot de passe
        $form = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('front_message_devis', ['id' => $user->getId()]);
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
