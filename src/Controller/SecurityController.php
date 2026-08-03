<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditProfileType;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Durée de validité d'un lien de définition ou de réinitialisation
     * de mot de passe.
     */
    private const TOKEN_TTL = '+1 hour';

    /**
     * @Route("/inscription", name="app_register")
     */
    public function renderRegister(
        Request $request,
        EntityManagerInterface $entityManager,
        RateLimiterFactory $registrationLimiter
    ): Response {
        $this->enforceLimit($registrationLimiter, $request);

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEmail($form->get('email')->getData());
            $user->setRoles(['ROLE_USER']);

            // Le mot de passe est défini à l'étape suivante, via un jeton
            // à usage unique. On ne transmet JAMAIS l'identifiant en clair
            // dans l'URL : sinon n'importe qui peut définir le mot de passe
            // de n'importe quel compte en devinant un identifiant.
            $token = bin2hex(random_bytes(32));
            $user->setResetToken($token);
            $user->setResetTokenExpireAt(new \DateTime(self::TOKEN_TTL));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('front_devis_set_password', [
                'token' => $token,
            ]);
        }

        return $this->render('security/register.html.twig', [
            'formRegister' => $form->createView(),
        ]);
    }

    /**
     * Définition du premier mot de passe, via jeton à usage unique.
     *
     * @Route("/continuer/{token}", name="front_devis_set_password", requirements={"token"="[a-f0-9]{64}"})
     */
    public function setPassword(
        string $token,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user || null === $user->getResetTokenExpireAt() || $user->getResetTokenExpireAt() < new \DateTime()) {
            $this->addFlash('error', 'Ce lien n\'est plus valide. Demandez-en un nouveau.');

            return $this->redirectToRoute('request_reset_password');
        }

        $form = $this->createForm(UserPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();

            if (empty($plainPassword)) {
                $this->addFlash('error', 'Le mot de passe ne peut pas être vide.');

                return $this->redirectToRoute('front_devis_set_password', ['token' => $token]);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            // Le jeton est consommé : le lien ne peut plus resservir.
            $user->setResetToken(null);
            $user->setResetTokenExpireAt(null);

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe est enregistré. Vous pouvez vous connecter.');

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
        if ($this->getUser()) {
            return $this->redirectToRoute('front_espace_client');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/utilisateur/modifier/profil", name="front_edit_profile")
     */
    public function editProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            $avatar = $form->get('avatar')->getData();
            if ($avatar) {
                $fileName = md5(uniqid('', true)).'.'.$avatar->guessExtension();
                $avatar->move($this->getParameter('uploads_directory'), $fileName);
                $user->setAvatar($fileName);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('front_espace_client');
        }

        return $this->render('front/profil/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mot-de-passe-oublier", name="request_reset_password")
     */
    public function requestResetPassword(
        Request $request,
        MailerInterface $mailer,
        EntityManagerInterface $em,
        RateLimiterFactory $passwordResetLimiter
    ): Response {
        if ($request->isMethod('POST')) {
            // Sans limitation, ce formulaire sert d'outil d'envoi de courriels
            // en masse et d'énumération de comptes.
            $this->enforceLimit($passwordResetLimiter, $request);

            if (!$this->isCsrfTokenValid('reset_password_request', (string) $request->request->get('_csrf_token'))) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            $email = (string) $request->request->get('email');
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $user->setResetToken($token);
                $user->setResetTokenExpireAt(new \DateTime(self::TOKEN_TTL));
                $em->flush();

                $resetLink = $this->generateUrl(
                    'reset_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $mailer->send(
                    (new TemplatedEmail())
                        ->from('contact@walidbelbeche.fr')
                        ->to($user->getEmail())
                        ->subject('Réinitialisation de votre mot de passe')
                        ->htmlTemplate('reset_password/email.html.twig')
                        ->context(['resetLink' => $resetLink])
                );
            }

            // Même réponse que le compte existe ou non : c'est ce qui empêche
            // de savoir si une adresse est inscrite.
            return $this->redirectToRoute('front_check_email');
        }

        return $this->render('reset_password/request.html.twig');
    }

    /**
     * @Route("/message-confirmation-email", name="front_check_email")
     */
    public function frontCheckEmail(): Response
    {
        return $this->render('reset_password/check_email.html.twig');
    }

    /**
     * @Route("/reset-password/{token}", name="reset_password", requirements={"token"="[a-f0-9]{64}"})
     */
    public function resetPassword(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user || null === $user->getResetTokenExpireAt() || $user->getResetTokenExpireAt() < new \DateTime()) {
            $this->addFlash('error', 'Ce lien de réinitialisation a expiré.');

            return $this->redirectToRoute('request_reset_password');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('reset_password', (string) $request->request->get('_csrf_token'))) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            $password = (string) $request->request->get('password');

            if (strlen($password) < 12) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 12 caractères.');

                return $this->redirectToRoute('reset_password', ['token' => $token]);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setResetToken(null);
            $user->setResetTokenExpireAt(null);
            $em->flush();

            $this->addFlash('success', 'Mot de passe modifié. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', ['token' => $token]);
    }

    /**
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('Cette méthode est interceptée par la clé logout du pare-feu.');
    }

    /**
     * Consomme un jeton du limiteur, ou renvoie une 429.
     */
    private function enforceLimit(RateLimiterFactory $factory, Request $request): void
    {
        $limiter = $factory->create($request->getClientIp() ?? 'anonymous');

        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(
                null,
                'Trop de tentatives. Réessayez dans quelques minutes.'
            );
        }
    }
}
