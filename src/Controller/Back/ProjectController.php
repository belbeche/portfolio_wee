<?php

namespace App\Controller\Back;

use App\Entity\Project;
use App\Form\ProjectType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /**
     * @Route("/admin/project", name="back_project_index")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {

        $projects = $entityManager->getRepository(Project::class)->findAll();

        return $this->render('back/project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    /**
     * @Route("/admin/project/new", name="back_project_new")
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $newProject = new Project();

        $form = $this->createForm(ProjectType::class, $newProject);

        if($request->isMethod('POST')){
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid())
            {
                $newProject->setCreatedAt(new \DateTime());
                $newProject->setUpdatedAt(new \DateTime());

                $entityManager->persist($newProject);
                $entityManager->flush();

                return $this->redirectToRoute('back_project_index');
            }
        }


        return $this->render('back/project/new.html.twig', [
            'projectForm' => $form->createView(),
        ]);
    }
}
