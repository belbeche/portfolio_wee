<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Subject;
use App\Form\Subject\Type\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormFactoryInterface;

class CommentService
{
    private EntityManagerInterface $entityManager;
    private FormFactoryInterface $formFactory;
    private Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->security = $security;
    }

    /**
     * Récupère les commentaires racines avec leurs réponses
     */
    public function getCommentsWithReplies(Subject $subject): array
    {
        return $this->entityManager->getRepository(Comment::class)->findBy(
            ['subject' => $subject, 'parent' => null],
            ['createdAt' => 'DESC']
        );
    }

    /**
     * Crée les formulaires de réponse pour chaque commentaire
     */
    public function createReplyForms(
        array $comments,
        Request $request,
        ?object $user,
        Subject $subject
    ): array {
        $replyForms = [];

        foreach ($comments as $comment) {
            $reply = new Comment();
            $reply->setParent($comment);

            $formReply = $this->formFactory->create(CommentType::class, $reply, [
                'action' => $request->getUri() . '#reply-' . $comment->getId(),
                'method' => 'POST',
            ]);

            $formReply->handleRequest($request);

            if ($formReply->isSubmitted() && $formReply->isValid()) {
                if (!$user) {
                    throw new \Exception('Vous devez être connecté pour répondre à un commentaire.');
                }

                $reply->setSubject($subject);
                $reply->setUser($user);
                $this->entityManager->persist($reply);
                $this->entityManager->flush();
            }

            $replyForms[$comment->getId()] = $formReply->createView();
        }

        return $replyForms;
    }
}
