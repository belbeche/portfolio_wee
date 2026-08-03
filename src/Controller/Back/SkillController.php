<?php

namespace App\Controller\Back;

use App\Entity\Skill;
use App\Form\SkillType;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Administration des competences.
 *
 * @Route("/admin/competences")
 */
class SkillController extends AbstractController
{
    /**
     * @Route("", name="back_skill_index", methods={"GET"})
     */
    public function index(SkillRepository $skills): Response
    {
        return $this->render('back/skill/index.html.twig', [
            'grouped' => $skills->findGroupedByFamily(),
            'total' => count($skills->findAll()),
        ]);
    }

    /**
     * @Route("/nouvelle", name="back_skill_new", methods={"GET","POST"})
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $skill = new Skill();
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($skill);
            $em->flush();
            $this->addFlash('success', 'Competence ajoutee.');

            return $this->redirectToRoute('back_skill_index');
        }

        return $this->render('back/skill/form.html.twig', [
            'form' => $form->createView(),
            'skill' => $skill,
            'mode' => 'Nouvelle competence',
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="back_skill_edit", requirements={"id"="\d+"}, methods={"GET","POST"})
     */
    public function edit(Skill $skill, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Competence mise a jour.');

            return $this->redirectToRoute('back_skill_index');
        }

        return $this->render('back/skill/form.html.twig', [
            'form' => $form->createView(),
            'skill' => $skill,
            'mode' => 'Modifier la competence',
        ]);
    }

    /**
     * @Route("/{id}/supprimer", name="back_skill_delete", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function delete(Skill $skill, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_skill_'.$skill->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $em->remove($skill);
        $em->flush();
        $this->addFlash('success', 'Competence supprimee.');

        return $this->redirectToRoute('back_skill_index');
    }

    /**
     * Bascule rapide de la visibilite, sans passer par le formulaire.
     *
     * @Route("/{id}/basculer", name="back_skill_toggle", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function toggle(Skill $skill, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('toggle_skill_'.$skill->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $skill->setVisible(!$skill->isVisible());
        $em->flush();

        return $this->redirectToRoute('back_skill_index');
    }
}
