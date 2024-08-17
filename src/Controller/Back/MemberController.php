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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MemberController extends AbstractController
{
    /**
     * @Route("/back/member", name="back_member_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(MemberRepository $memberRepository): Response
    {
        $members = $memberRepository->findAll();


        return $this->render('back/member/index.html.twig', [
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
                // Nécessaire pour inclure le nom du fichier dans l'URL de manière sécurisée
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

                // Déplacez le fichier dans le répertoire où les avatars sont stockés
                try {
                    $avatarFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception en cas de problème lors du déplacement du fichier
                    throw new \Exception('Une erreur est survenue lors du téléchargement de l\'avatar.');
                }

                // Mettre à jour la propriété 'avatar' pour stocker le nom du fichier
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
     * @Route("/back/member/{id}", name="back_member_show", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Member $member): Response
    {
        return $this->render('back/member/show.html.twig', [
            'member' => $member,
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
                // Si un nouvel avatar est uploadé, on génère un nom unique et on le déplace
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception en cas de problème lors du déplacement du fichier
                    throw new \Exception('Une erreur est survenue lors du téléchargement de l\'avatar.');
                }

                // Supprimer l'ancien avatar s'il existe
                if ($member->getAvatar()) {
                    $oldAvatarPath = $this->getParameter('uploads_directory').'/'.$member->getAvatar();
                    if (file_exists($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }
                }

                // Mettre à jour la propriété 'avatar' avec le nouveau nom de fichier
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
     * @Route("/back/member/{id}/delete", name="back_member_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(EntityManagerInterface $entityManager, $id): Response
    {
        $member = $entityManager->getRepository(Member::class)->find($id);

        if (!$member) {
            throw $this->createNotFoundException('Le membre avec l\'ID '.$id.' n\'existe pas.');
        }

        // Supprimer l'avatar associé s'il existe
        if ($member->getAvatar()) {
            $avatarPath = $this->getParameter('uploads_directory').'/'.$member->getAvatar();
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }

        $entityManager->remove($member);
        $entityManager->flush();

        return $this->redirectToRoute('back_member_index');
    }

}