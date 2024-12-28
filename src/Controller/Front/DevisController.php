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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Mpdf\Mpdf;

class DevisController extends AbstractController
{
    /**
    * @Route("/devis-en-ligne", name="front_devis_new")
    */
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, Security $security): Response
    {
        $devis = new Devis();

        // Créer le formulaire de devis
        $form = $this->createForm(DevisType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Accès restreint aux utilisateurs autorisés
            $this->denyAccessUnlessGranted('CREATE', $devis);

            // Définir le statut et le prix initial du devis
            $devis->setStatut('en_attente');
            $devis->setPrix(0);

            // Vérifier l'utilisateur connecté
            $user = $security->getUser();
            if ($user instanceof UserInterface) {
                $devis->setEmailFromUser($user);

                if ($user->getUserIdentifier()) {
                    $devis->setEmail($user->getUserIdentifier());
                    $devis->setUser($user);
                }
            }

            try {
                $entityManager->persist($devis);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la sauvegarde du devis.');
                return $this->redirectToRoute('front_devis_new');
            }

            // Générer le PDF du devis
            try {
                $html = $this->renderView('front/devis/show.html.twig', [
                    'devis' => $devis
                ]);

                $filename = 'devis_' . $devis->getId() . '.pdf';
                $mpdf = new Mpdf();
                $mpdf->WriteHTML($html);
                $pdfContent = $mpdf->Output('', 'S'); // Générer le PDF en tant que chaîne
            } catch (\Exception $e) {
                $this->addFlash('error', 'Impossible de générer le PDF du devis.');
                return $this->redirectToRoute('front_devis_new');
            }

            // Envoi de l'e-mail avec le récapitulatif du devis et le PDF en pièce jointe
            try {
                $email = (new TemplatedEmail())
                    ->from(new Address('contact@scriptzenit.fr', 'L\'équipe Scriptzenit'))
                    ->to($devis->getEmail())
                    ->bcc('wbelbeche.s@gmail.com')
                    ->subject('Récapitulatif de votre demande de devis')
                    ->htmlTemplate('front/devis/email.html.twig')
                    ->context([
                        'registredNumber' => $devis->getId(),
                        'emailAddress' => $devis->getEmail(),
                        'subject' => $devis->getTypeDeSiteWeb(),
                        'designWebsite' => $devis->getAttentesDesignWeb(),
                        'typeWeb' => $devis->getTypeDeSiteWeb(),
                        'message' => $devis->getDescriptionProjet()
                    ])
                    ->attach($pdfContent, $filename, 'application/pdf');

                $mailer->send($email);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de l\'email.');
                return $this->redirectToRoute('front_devis_new');
            }

            // Message de confirmation à l'utilisateur
            $this->addFlash('success', 'Votre demande a bien été prise en compte. Vérifiez votre adresse e-mail.');

            return $this->redirectToRoute('front_confirmation_estimate', [
                'id' => $devis->getId()
            ]);
        }

        return $this->render('front/devis/new.html.twig', [
            'formDevis' => $form->createView(),
        ]);
    }


    /**
     * @Route("/continuer/{id}", name="front_devis_set_password")
     */
    public function setPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher,$id): Response
    {
        // Vérifier si un utilisateur existe avec cet ID
        // $user = $entityManager->getRepository(User::class)->find($id);

        // Vérifier si un utilisateur existe avec cet ID
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);

        // Créer et traiter le formulaire pour le mot de passe
        $form = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if(!$this->getUser()){
                // Encodage du mot de passe uniquement si le champ de mot de passe est rempli
                $plainPassword = $form->get('password')->getData();

                // Encodage du mot de passe uniquement si le champ de mot de passe est rempli
                $plainPassword = $form->get('password')->getData();
                if ($plainPassword !== '') {
                    if (!empty($plainPassword)) {
                        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                        $user->setPassword($hashedPassword);
                    }
                }
            }

            // Enregistrement de l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirection vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front/devis/set_password.html.twig', [
            'form' => $form->createView(),
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
     * @Route("/rgertelecharger-devis/{id}", name="front_download_devis")
     * @IsGranted("ROLE_USER")
     */
    public function downloadDevis(EntityManagerInterface $entityManager, $id): Response
    {
        $currentUser = $this->getUser();

        $devis = $entityManager->getRepository(Devis::class)->find($id);

        $html = $this->renderView('front/devis/show.html.twig', [
            'devis' => $devis
        ]);

        $filename = 'devis_' . $currentUser->getId() . '.pdf';

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->Output($filename, 'D');

        return new Response();
    }

    /**
     * @Route("/devis/remerciement/{id}", name="front_confirmation_estimate")
     */
    public function messageConfirmation(
        EntityManagerInterface $entityManager,
        string $id
    ): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        return $this->render('front/devis/confirmation.html.twig', [
            'user' => $user,
        ]);
    }
}
