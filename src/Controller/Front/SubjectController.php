<?php
// app/Controller/Front;

namespace App\Controller\Front;

use App\Entity\Subject;
use App\Entity\Comment;
use App\Entity\Image;
use App\Form\Subject\Type\SubjectType;
use App\Form\Subject\Type\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class SubjectController extends AbstractController
{
    /**
     * @Route("/profil/assistance/Subject/nouveau", name="front_Subjects_new")
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @IsGranted("ROLE_USER")
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $Subject = new Subject();

        $form = $this->createForm(SubjectType::class, $Subject);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $Subject->setUser($this->getUser());
                $Subject->setSubjectId(rand(0,999999999));

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

                $entityManager->persist($Subject);

                $entityManager->flush();

                $this->addFlash('success', 'Demande en cours de traitement, ajouter en brouillon, en attente de validation.');

                return $this->redirectToRoute('front_Subjects_show', ['title' => $Subject->getTitle()]);
            }
        }

        return $this->render('front/subject/new.html.twig', [
            'form' => $form->createView(),
            'Subject' => $Subject,
        ]);
    }

    /**
     * @Route("/profil/assistance/Subjects/{title}", name="front_Subjects_show")
     * @return Response
     */
    public function show(
        EntityManagerInterface $entityManager,
        Request $request,
        Subject $Subject
    ): Response {
        // to get the comment in the Subject, we assign it in the variable $Subject
        $Subject = $entityManager->getRepository(Subject::class)->find($Subject->getId());

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $comment->setSubject($Subject);
                $comment->setUser($this->getUser());
                /* TODO: Répondre à un commentaire */
                // Ajouter la récursion pour le sous-commentaire avec un button
                /*dump($this->getUser());die();*/
                $entityManager->persist($comment);
                $entityManager->flush();

                /*return $this->redirectToRoute('front_Subjects_show', ['title' => $Subject->getTitle()]);*/
            }
        }

        $comments = $entityManager->getRepository(Comment::class)->findBy(
            [
                'Subject' => $Subject,
                'active' => true,
            ],
            ['id' => 'DESC']
        );

        return $this->render('front/subject/show.html.twig', [
            'Subject' => $Subject,
            'form' => $form->createView(),
            'comments' => $comments
        ]);
    }
}
