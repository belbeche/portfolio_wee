<?php
namespace App\Form\DataTransformer;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EmailToUserTransformer implements DataTransformerInterface
{
private EntityManagerInterface $entityManager;

public function __construct(EntityManagerInterface $entityManager)
{
$this->entityManager = $entityManager;
}

public function transform($user)
{
if (null === $user) {
return '';
}

return $user->getEmail();
}

public function reverseTransform($email)
{
if (!$email) {
return null;
}

$user = $this->entityManager
->getRepository(User::class)
->findOneBy(['email' => $email])
;

if (null === $user) {
throw new TransformationFailedException(sprintf('Cet utilisateur n\'existe pas ', $email));
}

return $user;
}
}
