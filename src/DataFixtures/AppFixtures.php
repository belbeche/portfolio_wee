<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 30; $i++) {
            $article = new Article();
            $user = new User();
            $article->setTitle('Titre n° : '.$i);
            $article->setContent("test".$i);
            $article->isActive(true);
            $article->settitle('title'.$i);
            $article->setUser($user->getUsername());

            $manager->persist($article);
        }
        $manager->flush();
    }
}
