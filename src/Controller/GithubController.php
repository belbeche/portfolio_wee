<?php

// src/Controller/GithubController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GithubController extends AbstractController
{
    /**
     * @Route("/connect/github", name="github_connect")
     */
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        /** @var GithubClient $client */
        $client = $clientRegistry->getClient('github');
        return $client->redirect(['read:user', 'user:email']);
    }
}
