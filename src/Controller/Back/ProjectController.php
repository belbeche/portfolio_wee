<?php

namespace App\Controller\Back;

use App\Entity\Image;
use App\Entity\Project;
use App\Form\ProjectType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
                $project->setUpdatedAt(new \DateTime());
    
                // Récupérer les fichiers d'images uploadés
                $imageFiles = $form->get('images')->getData();
    
                foreach ($imageFiles as $imageFile) {
                    if ($imageFile instanceof UploadedFile) {
                        $imageName = uniqid() . '.' . $imageFile->getClientOriginalExtension();
    
                        $imageFile->move(
                            $this->getParameter('images_directory'),
                            $imageName
                        );
    
                        $image = new Image();
                        $image->setName($imageName);
                        $project->addImage($image);
                    }
                }
    
                $entityManager->persist($project);
                $entityManager->flush();
    
                return $this->redirectToRoute('back_project_index');
            }
        }
    
        return $this->render('back/project/new.html.twig', [
            'projectForm' => $form->createView(),
            'project' => $project,
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
    ): Response {
        $form = $this->createForm(ProjectType::class, $project);
        $originalImages = new ArrayCollection();
    
        // Créer une copie des images originales du projet
        foreach ($project->getImages() as $image) {
            $originalImages->add($image);
        }
    
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $project->setUpdatedAt(new \DateTime());
    
                // Supprimer les images qui ont été supprimées du formulaire
                foreach ($originalImages as $image) {
                    if (!$project->getImages()->contains($image)) {
                        $entityManager->remove($image);
                    }
                }
    
                // Récupérer les fichiers d'images uploadés
                $imageFiles = $form->get('images')->getData();
    
                foreach ($imageFiles as $imageFile) {
                    if ($imageFile instanceof UploadedFile) {
                        $imageName = uniqid() . '.' . $imageFile->getClientOriginalExtension();
                        $imageExtension = $imageFile->getClientOriginalExtension();
                
                        $imageFile->move(
                            $this->getParameter('images_directory'),
                            $imageName
                        );
                
                        $image = new Image();
                        $image->setName($imageName);
                        $project->addImage($image);
                    }
                }
    
                $entityManager->persist($project);
                $entityManager->flush();
    
                return $this->redirectToRoute('back_project_index');
            }
        }
    
        return $this->render('back/project/edit.html.twig', [
            'projectForm' => $form->createView(),
            'project' => $project,
        ]);
    }
    /**
     * @Route("/admin/project/remove/{id}", name="back_project_remove")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function remove(
        Project $project,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        if ($project->getCreatedAt(true)) {
            $this->addFlash('success', "Le projet " . $project->getTitle() . " est supprimé avec success !");
            $entityManager->remove($project);
            $entityManager->flush();
        } else {
            $this->addFlash('success', "Le projet " . $project->getTitle() . " est inexistant ou a déjà été supprimé");
        }

        return $this->redirectToRoute('back_project_index');
    }
}
