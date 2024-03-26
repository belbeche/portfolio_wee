<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/api/register", name="api_register", methods={"POST"})
     */
    public function Register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {
        $data = json_decode($request->getContent(), true);
        // Utilisez $data pour remplir l'entité $user...

        if ($data !== null) {
            if (!isset($data['email'], $data['password'], $data['nom'], $data['prenom'])) {
                return $this->json(['error' => 'Certaines clés sont manquantes dans les données envoyées.']);
            }

            $user = new User();
            $user->setEmail($data['email']);
            $user->setPassword($hasher->hashPassword($user, $data['password']));
            $user->setNom($data['nom']);
            $user->setPrenom($data['prenom']);
            $entityManager->persist($user);
            $entityManager->flush();
        } else {
            return $this->json(['error' => true]);
        }


        return $this->json(['success' => true]);
    }

    /**
     * @Route("/api/login_check", name="api_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        // Récupérez l'erreur d'authentification, si elle existe
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error) {
            return new JsonResponse(['error' => $error->getMessage()], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Si l'authentification a réussi, le bundle JWT prendra le relais.
        // En cas de succès, un JWT sera retourné, donc aucun autre traitement n'est nécessaire ici.
        return new JsonResponse(['error' => 'Une erreur inattendue s\'est produite.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
