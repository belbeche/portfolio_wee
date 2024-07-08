<?php

namespace App\Controller\Front;

use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends AbstractController
{
    /**
     * @Route("/equipe", name="front_member_index")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $members = $entityManager->getRepository(Member::class)->findAll();

        return $this->render('front/about/index.html.twig', [
            'members' => $members
        ]);
    }
}