<?php

// src/Security/GithubUserProvider.php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class GithubUserProvider implements UserProviderInterface
{
    public function loadUserByUsername(string $username)
    {
        // Load user from database or external service by username
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        // Reload user from database or external service
    }

    public function supportsClass(string $class)
    {
        return User::class === $class;
    }

    // Ajustement de la signature de la méthode supports
    public function supports(Request $request): ?bool
    {
        // Logic to determine if this authenticator should be used based on the current request
    }
}
