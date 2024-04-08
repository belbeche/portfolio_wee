<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Devis;
use App\Form\EditProfileType;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("/inscription", name="app_register")
     */
    public function renderRegister(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('front_assistance');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(null);
            $user->setEmail($form->getData('email'));
            // Envoi de l'email
            $email = (new TemplatedEmail())
                ->from('contact@scriptzenit.fr')
                ->to($user->getEmail())
                ->subject('Récapitulatif inscription ScriptZenIT')
                ->bcc('wbelbeche.s@gmail.com')
                ->context([
                    'RegistredNumber' => $user->getId(),
                    'email_address' => $user->getEmail(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'civility' => $user->getCivility(),
                ])
                ->htmlTemplate('security/email.html.twig');
            $mailer->send($email);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('front_devis_set_password', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('security/register.html.twig', [
            'formRegister' => $form->createView(),
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
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/utilisateur/modifier/profil", name="front_edit_profile")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @return Response
     */
    public function EditProfile(Request $request,EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(EditProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encodage du mot de passe uniquement si le champ de mot de passe est rempli
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            // Gestion de l'avatar
            $avatar = $form->get('avatar')->getData();
            if ($avatar) {
                $fileName = md5(uniqid()) . '.' . $avatar->guessExtension();
                $avatar->move($this->getParameter('uploads_directory'), $fileName);
                $user->setAvatar($fileName);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('front_profile');
        }

        return $this->render('front/profil/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mot-de-passe-oublier", name="request_reset_password")
     */
    public function requestResetPassword(Request $request, MailerInterface $mailer, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $user->setResetToken($token);
                $user->setResetTokenExpireAt(new \DateTime('+1 hour'), new \DateTimeZone('Europe/Paris'));
                $em->flush();

                $resetLink = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                $email = (new TemplatedEmail())
                    ->from('contact@scriptzenit.fr')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('reset_password/email.html.twig')
                    ->context(['resetLink' => $resetLink]);

                $mailer->send($email);
            }

            // Rediriger vers une page de confirmation, même si l'email n'existe pas.
            return $this->redirectToRoute('front_check_email');
        }

        return $this->render('reset_password/request.html.twig');
    }

    /**
     * @Route("/message-confirmation-email", name="front_check_email")
     */
    public function frontCheckEmail()
    {

        return $this->render('reset_password/check_email.html.twig');
    }

    /**
     * @Route("/reset-password/{token}", name="reset_password")
     */
    public function resetPassword($token, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetTokenExpireAt() < new \DateTime()) {
            // Token invalide ou expiré.
            return $this->redirectToRoute('request_reset_password');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $hashedPassword = $passwordEncoder->encodePassword($user, $password);
            $user->setPassword($hashedPassword);
            $user->setResetToken(null);
            $user->setResetTokenExpireAt(null);
            $em->flush();

            // Rediriger vers la page de connexion avec un message de succès.
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', ['token' => $token]);
    }

    /**
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
