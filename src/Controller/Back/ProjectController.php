<?php

namespace App\Controller\Back;

use App\Entity\Images;
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

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $newProject->setCreatedAt(new \DateTime());
                $newProject->setUpdatedAt(new \DateTime());
                // On récupère les images transmises
                $images = $form->get('images_project')->getData();

                // Foreach on each image
                foreach ($images as $image) {
                    // We generate an hashed name with the extension
                    $file = md5(uniqid()) . '.' . $image->guessExtension();

                    // We copy to the upload folder the image
                    $image->move(
                        $this->getParameter('images_directory'),
                        $file
                    );

                    // We add image in the database and to the article
                    $img = new Images();
                    $img->setName($file);
                    $img->setImages($newProject);
                }

                $entityManager->persist($newProject);
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
                // On récupère les images transmises
                $images = $form->get('images_project')->getData();

                // Foreach on each image
                foreach ($images as $image) {
                    // We generate an hashed name with the extension
                    $file = md5(uniqid()) . '.' . $image->guessExtension();

                    // We copy to the upload folder the image
                    $image->move(
                        $this->getParameter('images_directory'),
                        $file
                    );

                    // We add image in the database and to the article
                    $img = new Images();
                    $img->setName($file);
                    $img->setImages($project);
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
