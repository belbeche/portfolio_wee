<?php
// app/Controller/Front;

namespace App\Controller\Front;

use App\Entity\Image;
use App\Entity\Reply;
use App\Entity\Comment;
use App\Entity\Subject;
use App\Form\ReplyType;
use App\Form\Subject\Type\CommentType;
use App\Form\Subject\Type\SubjectType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class SubjectController extends AbstractController
{
    /**
     * @Route("/profil/assistance/subject/nouveau", name="front_subjects_new")
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

                $entityManager->persist($subject);

                $entityManager->flush();

                $this->addFlash('success', 'Demande en cours de traitement, ajouter en brouillon, en attente de validation.');

                return $this->redirectToRoute('front_subject_show', [
                    'title' => $subject->getTitle(),
                    'commentId' => $subject->getId(),
                    'subjectTitle' => $subject->getTitle(),
                ]);
            }
        }

        return $this->render('front/subject/new.html.twig', [
            'form' => $form->createView(),
            'subject' => $subject,
        ]);
    }

    /**
    * @Route("/subject/{subjectTitle}", name="front_subject_show")
    */
    public function showSubject(string $subjectTitle, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le sujet
        $subject = $entityManager
            ->getRepository(Subject::class)
            ->findOneBy(['title' => $subjectTitle]);

        if (!$subject) {
            throw $this->createNotFoundException('Le sujet est introuvable.');
        }

        // Créer le formulaire pour ajouter un commentaire
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setSubject($subject);
            $comment->setUser($this->getUser());
            $comment->setCreatedAt(new \DateTime());

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');

            // Redirection pour éviter une double soumission du formulaire
            return $this->redirectToRoute('front_subject_show', ['subjectTitle' => $subjectTitle]);
        }

        // Charger les commentaires existants
        $comments = $entityManager
            ->getRepository(Comment::class)
            ->findBy(['subject' => $subject]);

        // Créer les formulaires de réponse pour chaque commentaire
        $replyFormsView = [];
        foreach ($comments as $comment) {
            $replyForm = $this->createForm(ReplyType::class);
            $replyFormsView[$comment->getId()] = $replyForm->createView();
        }

        // Rendre la vue avec les données nécessaires
        return $this->render('front/subject/show.html.twig', [
            'subject' => $subject,
            'comments' => $comments,
            'form' => $form->createView(),
            'replyForms' => $replyFormsView,
        ]);
    }


    /**
    * @Route("/profil/assistance/subject/{subjectTitle}/comment/{commentId}/reply", name="front_subject_reply", methods={"POST"})
    */
    public function replyToComment(
        Request $request,
        string $subjectTitle,
        int $commentId,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer le sujet
        $subject = $entityManager->getRepository(Subject::class)->findOneBy(['title' => $subjectTitle]);

        // Récupérer le commentaire
        $comment = $entityManager->getRepository(Comment::class)->find($commentId);

        if (!$subject || !$comment) {
            throw $this->createNotFoundException('Le sujet ou le commentaire est introuvable.');
        }

        // Récupérer le contenu de la réponse
        $content = $request->request->get('content');

        if (empty($content)) {
            $this->addFlash('error', 'Le contenu de la réponse ne peut pas être vide.');
            return $this->redirectToRoute('front_subject_show', ['subjectTitle' => $subjectTitle]);
        }

        // Créer la réponse
        $reply = new Reply();
        $reply->setContent($content);
        $reply->setUser($this->getUser());
        $reply->setCreatedAt(new \DateTime());
        $reply->setParentComment($comment);

        // Sauvegarder la réponse
        $entityManager->persist($reply);
        $entityManager->flush();

        // Rediriger avec un message de succès
        $this->addFlash('success', 'Votre réponse a été ajoutée.');
        return $this->redirectToRoute('front_subject_show', [
            'subjectTitle' => $subjectTitle,
            'commentId' => $commentId,
            'subject' => $subject,
        ]);
    }
}
