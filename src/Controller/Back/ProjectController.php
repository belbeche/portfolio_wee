<?php

namespace App\Controller\Back;

use App\Entity\Image;
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
        $project = new Project();

        $form = $this->createForm(ProjectType::class, $project);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $project->setCreatedAt(new \DateTime('NOW'));
                $project->setUpdatedAt(new \DateTime('NOW'));

                $imageFile = $form->get('image')->getData();

                if ($imageFile) {

                    $imageName = uniqid().'.'.$imageFile->getData()->getClientOriginalExtension();

                    /*$imageFile->move($this->getParameter('images_directory'), $imageName);*/

                    $imageFile->getData()->move(
                        $this->getParameter('images_directory'),
                        $imageName
                    );

                    $image = new Image();
                    $image->setName($imageName);
                    $image->setData($imageFile->getData()->getClientOriginalExtension());
                    $project->setImage($image);
                }

                $entityManager->persist($project);
                $entityManager->flush();

                return $this->redirectToRoute('back_project_index');
            }
        }


        return $this->render('back/project/new.html.twig', [
            'projectForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/project/edit/{id}", name="back_project_edit")
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(
        Project                $project,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {

        $form = $this->createForm(ProjectType::class, $project);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $project->setUpdatedAt(new \DateTime());

                $imageFile = $form->get('imageFile')->getData();

                if ($imageFile) {
                    $image = new Image();
                    $image->setName($imageFile->getClientOriginalName());
                    $image->setData(file_get_contents($imageFile->getPathName()));
                    $project->setImageFile($image->getName());
                }

                $entityManager->persist($project);
                $entityManager->flush();

                return $this->redirectToRoute('back_project_index');
            }
        }

        return $this->render('back/project/edit.html.twig', [
            'projectEdit' => $form->createView(),
            'project' => $project,
        ]);
    }

}
