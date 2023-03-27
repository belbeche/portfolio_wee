<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Devis;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("/inscription", name="app_register")
     * 
     */
    public function Register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
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


                $entityManager->persist($user);

                $entityManager->flush();

                return $this->redirectToRoute('front_home');
            }
        }

        return $this->render('security/register.html.twig', [
            'formRegister' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
