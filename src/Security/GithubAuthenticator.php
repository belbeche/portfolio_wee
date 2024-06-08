<?php

// src/Security/GithubAuthenticator.php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use League\OAuth2\Client\Provider\Github as GithubProvider;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class GithubAuthenticator extends AbstractGuardAuthenticator
{
    private $router;
    private $provider;
    private $em;

    public function __construct(RouterInterface $router, EntityManagerInterface $em)
    {
        $this->router = $router;
        $this->em = $em;
        $this->provider = new GithubProvider([
            'clientId'          => $_ENV['GITHUB_CLIENT_ID'],
            'clientSecret'      => $_ENV['GITHUB_CLIENT_SECRET'],
            'redirectUri'       => $_ENV['GITHUB_REDIRECT_URI']
        ]);
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'connect_github_check';
    }

    public function getCredentials(Request $request)
    {
        return [
            'code' => $request->query->get('code'),
            'state' => $request->query->get('state'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!$credentials['code']) {
            return null;
        }

        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $credentials['code']
            ]);

            $githubUser = $this->provider->getResourceOwner($token);
            $githubId = $githubUser->getId();
            $username = $githubUser->getNickname();

            // Vérifier si l'utilisateur existe déjà
            $user = $this->em->getRepository(User::class)->findOneBy(['githubId' => $githubId]);

            if (!$user) {
                // Créer un nouvel utilisateur
                $user = new User();
                $user->setGithubId($githubId);
                $user->setUsername($username);

                $this->em->persist($user);
                $this->em->flush();
            }

            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // Aucune vérification nécessaire, l'utilisateur a déjà été authentifié par GitHub
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // Rediriger l'utilisateur après une authentification réussie
        return new RedirectResponse($this->router->generate('front_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // Rediriger l'utilisateur en cas d'échec de l'authentification
        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // Rediriger l'utilisateur vers la page de connexion si nécessaire
        return new RedirectResponse($this->router->generate('app_login'));
    }

    /**
     * Does this method support remember me cookies?
     *
     * Remember me cookie will be set if *all* of the following are met:
     *  A) This method returns true
     *  B) The remember_me key under your firewall is configured
     *  C) The "remember me" functionality is activated. This is usually
     *      done by having a _remember_me checkbox in your form, but
     *      can be configured by the "always_remember_me" and "remember_me_parameter"
     *      parameters under the "remember_me" firewall key
     *  D) The onAuthenticationSuccess method returns a Response object
     *
     * @return bool
     */
    public function supportsRememberMe()
    {
        // TODO: Implement supportsRememberMe() method.
    }
}
