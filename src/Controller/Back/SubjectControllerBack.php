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

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return mixed
     * @Route("/admin/subjects", name="back_subjects_list")
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

        return $this->render('back/subject/list.html.twig', [
            'Subjects' => $Subjects,
        ]);
    }

    /**
     * @Route("/admin/subjects/new", name="back_subjects_new")
     *
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(
        Request $request
    ): Response {
        $subject = new Subject();

        $form = $this->createForm(SubjectType::class, $subject);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $subject->setUser($this->getUser());

                $subject->setCreatedAt(new \DateTime);

                $subject->setIsPublished(false);

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
                    $subject->addImage($img);
                    $img->setSubject($subject);
                }


                $this->entityManager->persist($subject);

                $this->entityManager->flush();

                $this->addFlash('success', 'Sujet ajouté avec succéss');

                return $this->redirectToRoute('back_home_support');
            }
        }

        return $this->render('back/subject/new.html.twig', [
            'form' => $form->createView(),
            'subject' => $subject,
        ]);
    }

    /**
     * @Route("/publier/{title}", name="back_subjects_publish")
     * isGranted("ROLE_ADMIN")
     */
    public function publish(Subject $subject)
    {
        if (
            $subject->getImages() !== null && $subject->getImages() !== ''
            && $subject->getContent() !== null && $subject->getContent() !== ''
            && $subject->getCategories() !== null
            && $subject->getTitle() !== null && $subject->getTitle() !== ''
        ) {
            $subject->setPublishedAt(new \DateTime);

            $subject->setIsPublished(true);

            $this->addFlash('success', 'Sujet publié avec succéss');

            $this->entityManager->persist($subject);
            $this->entityManager->flush();

            // We redirect to the subject display page if everything is OK
            return $this->redirectToRoute(
                'back_subjects_show',
                ['title' => $subject->getTitle()]
            );
        }

        return $this->redirectToRoute(
            'back_subjects_edit',
            ['title' => $subject->getTitle()]
        );
    }

    /**
     * @Route("/admin/subjects/show/{title}", name="back_subjects_show")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     * @throws NotFoundHttpException
     */
    public function show(
        Subject                $subject,
        EntityManagerInterface $entityManager,
    ): Response {
        $subject = $entityManager->getRepository(Subject::class)->find($subject);

        return $this->render('back/subject/show.html.twig', [
            'subject' => $subject,
        ]);
    }

    /**
     * @Route("/admin/subjects/edit/{title}", name="back_subjects_edit")
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(
        Subject $subject,
        Request $request
    ): Response {

        $form = $this->createForm(SubjectType::class, $subject);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $subject->setUpdatedAt(new \DateTime("NOW"));

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
                    $subject->addImage($img);
                    $img->setSubject($subject);
                }

                /*if ($Subject->getPublishedAt() === null) {
                    // on renseigne le title de l'Subject
                    $Subject->settitle(
                        strtolower($Subject->gettitle())
                    );
                }*/


                $this->entityManager->persist($subject);
                $this->entityManager->flush();

                return $this->redirectToRoute('back_home_support');
            }
        }

        return $this->render('back/subject/edit.html.twig', [
            'form' => $form->createView(),
            'subject' => $subject,
        ]);
    }


    /**
     * @Route("/admin/subjects/disable/{id}", name="back_subjects_disable")
     * @param $id
     * @return mixed
     * @IsGranted("ROLE_ADMIN")
     */
    public function disable(
        $id
    ): Response {
        $subject = $this->entityManager->getRepository(Subject::class)->find($id);
        $subject->setActive(!$subject->isActive());
        $this->entityManager->persist($subject);
        $this->entityManager->flush();
        if ($subject->isActive() == true) {
            $this->addFlash('warning', "Le sujet " . $subject->getTitle() . " a bien était désactiver :[");
        } else {
            $this->addFlash('success', "Le sujet " . $subject->getTitle() . " a bien était réactiver :]");
        }

        return $this->redirectToRoute('back_subjects_list');
    }

    /**
     * @Route("/admin/subject/remove/{title}", name="back_subjects_remove")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function remove(
        Subject $subject,
        Request $request
    ): Response {

        if ($subject->getCreatedAt(true)) {
            $this->addFlash('success', "Le sujet " . $subject->getTitle() . " est supprimé avec success !");
            $this->entityManager->remove($subject);
            $this->entityManager->flush();
        } else {
            $this->addFlash('success', "Le sujet " . $subject->getTitle() . " est activé, disactivez-le puis réessayer, merci.");
        }

        return $this->redirectToRoute('back_home_support');
    }

    /**
     * @Route("/back/subjects/remove/image/{name}", name="back_subjects_remove_image", methods={"DELETE"})
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
