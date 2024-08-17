<?php

namespace App\Controller\Front;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends AbstractController
{
    /**
     * @Route("/equipe", name="front_member_index")
     */
    public function index(MemberRepository $memberRepository): Response
    {
        // Récupération de tous les membres
        $members = $memberRepository->findAll();

        // Initialisation du tableau pour regrouper les membres par catégorie
        $groupedMembers = [];

        // Organisation des membres par catégorie
        foreach ($members as $member) {
            $category = $member->getCategory();

            // Créer une nouvelle catégorie si elle n'existe pas encore
            if (!isset($groupedMembers[$category])) {
                $groupedMembers[$category] = [];
            }

            // Ajouter le membre à la catégorie correspondante
            $groupedMembers[$category][] = $member;
        }

        // Transmission des données au template
        return $this->render('front/about/index.html.twig', [
            'groupedMembers' => $groupedMembers,
        ]);
    }
}
