<?php

namespace App\Controller\Back;

use App\Entity\Member;
use App\Form\Member\Type\MemberFormType;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

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
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberFormType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
    public function edit(Request $request, EntityManagerInterface $entityManager,Member $member): Response
    {
        $form = $this->createForm(MemberFormType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
    public function delete(EntityManagerInterface $entityManager,$id): Response
    {
        $member = $entityManager->getRepository(Member::class)->find($id);

        $entityManager->remove($member);
        $entityManager->flush();

        return $this->redirectToRoute('back_member_index');
    }
}