<?php

namespace App\Controller\Back;

use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Form\Member\Type\MemberFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class MemberController extends AbstractController
{
    /**
     * @Route("/back/member", name="back_member_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(MemberRepository $memberRepository): Response
    {
        $members = $memberRepository->findAll();

        // Organiser les membres par catégorie
        $groupedMembers = [];

        foreach ($members as $member) {
            $category = $member->getCategory();

            // Créer une nouvelle catégorie si elle n'existe pas encore
            if (!isset($groupedMembers[$category])) {
                $groupedMembers[$category] = [];
            }

            // Ajouter le membre à la catégorie correspondante
            $groupedMembers[$category][] = $member;
        }

        return $this->render('back/member/index.html.twig', [
            'groupedMembers' => $groupedMembers,
            'members' => $members,
        ]);
    }

    /**
     * @Route("/back/member/new", name="back_member_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberFormType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier d'avatar
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Une erreur est survenue lors du téléchargement de l\'avatar.');
                }

                $member->setAvatar($newFilename);
            }

            $entityManager->persist($member);
            $entityManager->flush();

            return $this->redirectToRoute('back_member_index');
        }

        return $this->render('back/member/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/back/member/{id}/edit", name="back_member_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, Member $member, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(MemberFormType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier d'avatar
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Une erreur est survenue lors du téléchargement de l\'avatar.');
                }

                // Supprimer l'ancien avatar s'il existe
                if ($member->getAvatar()) {
                    $oldAvatarPath = $this->getParameter('uploads_directory').'/'.$member->getAvatar();
                    if (file_exists($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }
                }

                $member->setAvatar($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('back_member_index');
        }

        return $this->render('back/member/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/back/member/{id}/delete", name="back_member_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, Member $member): Response
    {
        if ($this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token'))) {
            // Supprimer l'avatar associé s'il existe
            if ($member->getAvatar()) {
                $avatarPath = $this->getParameter('uploads_directory').'/'.$member->getAvatar();
                if (file_exists($avatarPath)) {
                    unlink($avatarPath);
                }
            }

            $entityManager->remove($member);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_member_index');
    }
}
