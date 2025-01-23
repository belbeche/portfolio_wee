<?php
// app/Controller/Front;


namespace App\Controller\Front;


use App\Entity\Image;
use App\Entity\Comment;
use App\Entity\Subject;
use App\Entity\Category;
use App\Service\CommentService;
use App\Repository\CommentRepository;
use App\Form\Subject\Type\CommentType;
use App\Form\Subject\Type\SubjectType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class SubjectController extends AbstractController
{

    /**
     * @Route("/support", name="front_subject_list")
     */
    public function listSubjects(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les sujets depuis la base de données
        $subjects = $entityManager->getRepository(Subject::class)->findAll();

        return $this->render('front/subject/list.html.twig', [
            'subjects' => $subjects,
        ]);
    }

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
                        $this->getParameter('subject_directory'),
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
                    'subjectId' => $subject->getId(),
                ]);
            }
        }

        return $this->render('front/subject/new.html.twig', [
            'form' => $form->createView(),
            'subject' => $subject,
        ]);
    }

    /**
    * @Route("/utilisateur/sujet/{subjectId}", name="front_subject_show")
    */
    public function showSubject(
        string $subjectId,
        Request $request,
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository
    ): Response {
        // Récupération du sujet
        $subject = $entityManager->getRepository(Subject::class)->find($subjectId);
        if (!$subject) {
            throw $this->createNotFoundException('Le sujet est introuvable.');
        }

        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Création du formulaire de commentaire principal
        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            if (!$user) {
                $this->addFlash('error', 'Vous devez être connecté pour ajouter un commentaire.');
                return $this->redirectToRoute('app_login');
            }

            $comment->setSubject($subject);
            $comment->setUser($user);
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');
            return $this->redirectToRoute('front_subject_show', ['subjectId' => $subjectId], 303);
        }

        // Récupération des commentaires racines et de leurs réponses
        // $comments = $commentRepository->findCommentsWithReplies($subject);
        // dd($comments);  // Debug pour voir les résultats

        $comments = $commentRepository->findBy(['subject' => $subject, 'parent' => null], ['createdAt' => 'DESC']);

        // Gestion des réponses aux commentaires
        $replyForms = [];
        foreach ($comments as $comment) {
            $reply = new Comment();
            $reply->setParent($comment); // Lier la réponse au commentaire parent

            $formReply = $this->createForm(CommentType::class, $reply, [
                'action' => $this->generateUrl('front_subject_show', ['subjectId' => $subjectId]) . '#reply-' . $comment->getId(),
                'method' => 'POST',
            ]);

            $formReply->handleRequest($request);

            // Vérification que le formulaire de réponse a été soumis
            if ($formReply->isSubmitted() && $formReply->isValid()) {
                if (!$user) {
                    $this->addFlash('error', 'Vous devez être connecté pour répondre à un commentaire.');
                    return $this->redirectToRoute('app_login');
                }

                $reply->setSubject($subject);
                $reply->setUser($user);
                $entityManager->persist($reply);
                $entityManager->flush();

                $this->addFlash('success', 'Réponse ajoutée avec succès.');
                return $this->redirectToRoute('front_subject_show', ['subjectId' => $subjectId], 303);
            }

            // Sauvegarde des formulaires de réponse pour chaque commentaire
            $replyForms[$comment->getId()] = $formReply->createView();
        }

        return $this->render('front/subject/show.html.twig', [
            'subject' => $subject,
            'comments' => $comments, 
            'formComment' => $formComment->createView(),
            'replyForms' => $replyForms,
        ]);
    }

    /**
     * @Route("/utilisateur/support/{id}", name="front_user_subject")
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function mySubject(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator)
    {

        $data = $entityManager->getRepository(Subject::class)->findBySubject();
        $user = $this->getUser();

        $subjects = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            4
        );

        $categories = $entityManager->getRepository(Category::class)->findAll();

        return $this->render('front/profil/profil_subject.html.twig', [
            'subjects' => $subjects,
            'categories' => $categories,
            'user' => $user
        ]);
    }
}
