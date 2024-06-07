<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use League\OAuth2\Client\Provider\Github as GithubOauth;
use Symfony\Component\Routing\Annotation\Route;

class GithubController extends AbstractController
{
    private $provider;

    public function __construct()
    {
        $this->provider = new GithubOauth([
            'clientId'          => $_ENV['GITHUB_CLIENT_ID'],
            'clientSecret'      => $_ENV['GITHUB_CLIENT_SECRET'],
            'redirectUri'       => $_ENV['GITHUB_REDIRECT_URI']
        ]);
    }

    /**
     * @Route("/oauth/github", name="github_oauth")
     */
    public function oauth(Request $request, SessionInterface $session): Response
    {
        if (!$request->query->has('code')) {
            return $this->redirectToGithub($session);
        }

        if (!$request->query->has('state') || $request->query->get('state') !== $session->get('oauth2state')) {
            $session->remove('oauth2state');
            return new Response('Invalid state', Response::HTTP_BAD_REQUEST);
        }

        return $this->handleGithubCallback($request, $session);
    }

    /**
     * @Route("/oauth/check/github", name="github_check")
     */
    public function handleGithubCallback(Request $request, SessionInterface $session): Response
    {
        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->query->get('code')
            ]);

            $user = $this->provider->getResourceOwner($token);
            $username = $user->getNickname();

            return new Response(sprintf('Hello %s!', $username));
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function redirectToGithub(SessionInterface $session): Response
    {
        $authUrl = $this->provider->getAuthorizationUrl();
        $session->set('oauth2state', $this->provider->getState());
        return $this->redirect($authUrl);
    }
}
