<?php

namespace App\Controller\Back;

use App\Entity\Subject;
use App\Entity\Image;
use App\Form\Subject\Type\SubjectType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Component\Pager\PaginatorInterface;


class SubjectControllerBack extends AbstractController
{

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return mixed
     * @Route("/admin/Subjects", name="back_Subjects_list")
     * @IsGranted("ROLE_ADMIN")
     */
    public function list(
        Request $request,
        PaginatorInterface $paginator
    ) {

        $data = $this->entityManager->getRepository(Subject::class)->findAll();

        $Subjects = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('back/Subject/list.html.twig', [
            'Subjects' => $Subjects,
        ]);
    }

    /**
     * @Route("/admin/Subjects/new", name="back_Subjects_new")
     *
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(
        Request $request
    ): Response {
        $Subject = new Subject();

        $form = $this->createForm(SubjectType::class, $Subject);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $Subject->setUser($this->getUser());

                $Subject->setCreatedAt(new \DateTime);

                $Subject->setIsPublished(false);

                $images = $form->get('images')->getData();

                foreach ($images as $image) {
                    // We generate a new file name
                    $file = md5(uniqid()) . '.' . $image->guessExtension();

                    // On copie le fichier dans le dossier uploads

                    $image->move(
                        $this->getParameter('images_directory'),
                        $file
                    );

                    // We add image in the database and to the Subject
                    $img = new Image();
                    $img->setName($file);
                    $Subject->addImage($img);
                    $img->setSubject($Subject);
                }


                $this->entityManager->persist($Subject);

                $this->entityManager->flush();

                $this->addFlash('success', 'Subject ajouté avec succéss');

                return $this->redirectToRoute('back_home_support');
            }
        }

        return $this->render('back/Subject/new.html.twig', [
            'form' => $form->createView(),
            'Subject' => $Subject,
        ]);
    }

    /**
     * @Route("/publier/{title}", name="back_Subjects_publish")
     */
    public function publish(Subject $Subject)
    {
        if (
            $Subject->getImages() !== null && $Subject->getImages() !== ''
            && $Subject->getContent() !== null && $Subject->getContent() !== ''
            && $Subject->getCategories() !== null
            && $Subject->getTitle() !== null && $Subject->getTitle() !== ''
        ) {
            $Subject->setPublishedAt(new \DateTime);

            $Subject->setIsPublished(true);

            $this->addFlash('success', 'Sujet publié avec succéss');

            $this->entityManager->persist($Subject);
            $this->entityManager->flush();

            // We redirect to the Subject display page if everything is OK
            return $this->redirectToRoute(
                'back_Subjects_show',
                ['title' => $Subject->getTitle()]
            );
        }

        return $this->redirectToRoute(
            'back_Subjects_edit',
            ['title' => $Subject->getTitle()]
        );
    }

    /**
     * @Route("/admin/Subjects/show/{title}", name="back_Subjects_show")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     * @throws NotFoundHttpException
     */
    public function show(
        Subject                $Subject,
        EntityManagerInterface $entityManager,
    ): Response {
        $Subject = $entityManager->getRepository(Subject::class)->find($Subject);

        if (!$Subject) {
            throw $this->createNotFoundException('Subject not found');
        }

        return $this->render('back/Subject/show.html.twig', [
            'Subject' => $Subject,
        ]);
    }

    /**
     * @Route("/admin/Subjects/edit/{title}", name="back_Subjects_edit")
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(
        Subject $Subject,
        Request $request
    ): Response {

        $form = $this->createForm(SubjectType::class, $Subject);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $Subject->setUpdatedAt(new \DateTime("NOW"));

            if ($form->isSubmitted() && $form->isValid()) {
                $images = $form->get('images')->getData();

                foreach ($images as $image) {
                    // We generate a new file name
                    $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                    // On copie le fichier dans le dossier uploads
                    $image->move(
                        $this->getParameter('images_directory'),
                        $fichier
                    );

                    // On crée l'image dans la base de données
                    $img = new Image();
                    $img->setName($fichier);
                    $Subject->addImage($img);
                    $img->setSubject($Subject);
                }

                /*if ($Subject->getPublishedAt() === null) {
                    // on renseigne le title de l'Subject
                    $Subject->settitle(
                        strtolower($Subject->gettitle())
                    );
                }*/


                $this->entityManager->persist($Subject);
                $this->entityManager->flush();

                return $this->redirectToRoute('back_home_support');
            }
        }

        return $this->render('back/Subject/edit.html.twig', [
            'form' => $form->createView(),
            'Subject' => $Subject,
        ]);
    }


    /**
     * @Route("/admin/Subjects/disable/{id}", name="back_Subjects_disable")
     * @param $id
     * @return mixed
     * @IsGranted("ROLE_ADMIN")
     */
    public function disable(
        $id
    ): Response {
        $Subject = $this->entityManager->getRepository(Subject::class)->find($id);
        $Subject->setActive(!$Subject->isActive());
        $this->entityManager->persist($Subject);
        $this->entityManager->flush();
        if ($Subject->isActive() == true) {
            $this->addFlash('warning', "L'Subject " . $Subject->getTitle() . " a bien était désactiver :[");
        } else {
            $this->addFlash('success', "L'Subject " . $Subject->getTitle() . " a bien était réactiver :]");
        }

        return $this->redirectToRoute('back_Subjects_list');
    }

    /**
     * @Route("/admin/Subject/remove/{title}", name="back_Subjects_remove")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function remove(
        Subject $Subject,
        Request $request
    ): Response {

        if ($Subject->getCreatedAt(true)) {
            $this->addFlash('success', "L'Subject " . $Subject->getTitle() . " est supprimé avec success !");
            $this->entityManager->remove($Subject);
            $this->entityManager->flush();
        } else {
            $this->addFlash('success', "L'Subject " . $Subject->getTitle() . " est activé, disactivez-le puis réessayer, merci.");
        }

        return $this->redirectToRoute('back_home_support');
    }

    /**
     * @Route("/back/Subjects/remove/image/{name}", name="back_Subjects_remove_image", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteImage(Image $image, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // We check if the token is valid
        if ($this->isCsrfTokenValid('delete' . $image->getName(), $data['_token'])) {

            // We get the name of the image
            $nom = $image->getName();

            // We delete the file
            unlink($this->getParameter('images_directory') . '/' . $nom);

            // We delete the entry from the database

            $this->entityManager->remove($image);
            $this->entityManager->flush();

            // We answer in json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}
