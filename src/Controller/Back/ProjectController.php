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
use Symfony\Component\HttpFoundation\JsonResponse;
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
                $this->extracted($form, $project, $entityManager);

                return $this->redirectToRoute('back_project_show', [
                    'id' => $project->getId()
                ]);
            }
        }

        return $this->render('back/project/new.html.twig', [
            'projectForm' => $form->createView(),
            'project' => $project,
        ]);
    }
    /**
     * @Route("/admin/project/show/{id}", name="back_project_show")
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(EntityManagerInterface $entityManager,$id): Response
    {

        $project = $entityManager->getRepository(Project::class)->find($id);


        return $this->render('back/project/show.html.twig', [
            'project' => $project,
        ]);
    }

    /**
     * @Route("/admin/project/edit/{id}", name="back_project_edit")
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     * @throws \Exception
     */
    public function edit(
        Project                $project,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response {
        $originalImages = new ArrayCollection();

        // Créer une copie des images originales du projet
        foreach ($project->getImages() as $image) {
            $originalImages->add($image);
        }

        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $project->setUpdatedAt((new \DateTime('now', new \DateTimeZone('Europe/Paris'))));

            // Supprimer les images qui ont été supprimées du formulaire
            foreach ($originalImages as $image) {
                if (!$project->getImages()->contains($image)) {
                    $entityManager->remove($image);
                }
            }

            // Récupérer les fichiers d'images uploadés
            $this->extracted($form, $project, $entityManager);

            return $this->redirectToRoute('back_project_index');
        }


        return $this->render('back/project/edit.html.twig', [
            'projectForm' => $form->createView(),
            'project' => $project,
        ]);
    }

    /**
     * @Route("/admin/project/remove/{id}", name="back_project_remove")
     * @param Project $project
     * @param Request $request
     * @param EntityManagerInterface $entityManager
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

    /**
     * @Route("/back/project/remove/image/{name}", name="back_project_remove_image", methods={"GET","DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteImage(Image $image, Request $request,EntityManagerInterface $entityManager)
    {
        $data = json_decode($request->getContent(), true);

        // We check if the token is valid
        if ($this->isCsrfTokenValid('delete' . $image->getName(), $data['_token'])) {

            // We get the name of the image
            $nom = $image->getName();

            // We delete the file
            unlink($this->getParameter('images_directory') . '/' . $nom);

            // We delete the entry from the database

            $entityManager->remove($image);
            $entityManager->flush();

            // We answer in json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param Project $project
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    public function extracted(\Symfony\Component\Form\FormInterface $form, Project $project, EntityManagerInterface $entityManager): void
    {
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
    }
}
