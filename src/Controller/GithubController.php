<?php

// src/Controller/GithubController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GithubController extends AbstractController
{
    /**
     * @Route("/connect/github/check", name="connect_github_check")
     */
    public function connectCheckAction()
    {
        // This is handled by the authenticator
    }
}
