<?php

namespace App\Security;

use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GithubAuthenticator extends OAuth2Authenticator
{
    private $clientRegistry;
    private $router;

    public function __construct(ClientRegistry $clientRegistry, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'oauth_check';
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getGithubClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        $githubUser = $this->getGithubClient()
            ->fetchUserFromToken($credentials);

        $email = $githubUser->getEmail();

        // ... fetch the user from the database or create it if necessary

        return $user;
    }

    private function getGithubClient()
    {
        return $this->clientRegistry->getClient('github');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Handle failure
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        // Handle success
    }
}
