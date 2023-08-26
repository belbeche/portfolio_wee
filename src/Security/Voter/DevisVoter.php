<?php

namespace App\Security\Voter;

use App\Entity\Devis;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DevisVoter extends Voter
{
    protected function supports(string $attribute, $subject)
    {
        return $attribute === 'CREATE' && $subject instanceof Devis;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        /** @var Devis $devis */
        $devis = $subject;

        // Récupérez l'UUID depuis la requête
        $requestUuid = $devis->getId();

        return $devis->getId() === $requestUuid;
    }
}