<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Devis;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("/inscription", name="app_register")
     * 
     */
    public function Register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher,MailerInterface $mailer): Response
    {

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPassword($hasher->hashpassword(
                    $user,
                    $form->get('password')->getData()
                ));
                $user->setRoles(['ROLE_USER']);


                $email = (new TemplatedEmail())
                    ->from('wbelbeche.s@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Récapitulatif inscription ScriptZenIT')
                    ->bcc('wbelbeche.s@gmail.com')
                    ->context([
                        'RegistredNumber' => $user->getId(),
                        'email_address' => $user->getEmail(),
                        'nom' => $user->getNom(),
                        'prenom' => $user->getPrenom(),
                        'civility' => $user->getCivility(),
                        'password' => $user->getPassword(),
                    ])
                    ->htmlTemplate('security/email.html.twig');

                $mailer->send($email);

                $entityManager->persist($user);

                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/register.html.twig', [
            'formRegister' => $form->createView(),
        ]);
    }

    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('front_assistance');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
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
                    ->from('wbelbeche.s@gmail.com')
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
