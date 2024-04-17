<?php

namespace App\DataFixtures;

use App\Entity\Subject;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 30; $i++) {
            $Subject = new Subject();
            $user = new User();
            $Subject->setTitle('Titre n° : '.$i);
            $Subject->setContent("test".$i);
            $Subject->isActive(true);
            $Subject->settitle('title'.$i);
            $Subject->setUser($user->getUsername());

            $manager->persist($Subject);
        }
        $manager->flush();
    }
}
