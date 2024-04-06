<?php

namespace App\Controller\Back;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/admin/comments", name="back_comments_list")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function list(EntityManagerInterface $entityManager , PaginatorInterface $paginator, Request $request): Response
    {

        $data = $entityManager->getRepository(Comment::class)->findAll();

        $comments = $paginator->paginate(
            $data,
            $request->query->getInt('page',1),
            12
        );

        return $this->render('back/comment/list.html.twig', [
            'comments' => $comments,
        ]);
    }

    /**
     * @Route("/admin/commentaires/afficher/{id}", name="back_comments_show")
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(EntityManagerInterface $entityManager,$id)
    {
        $comment = $entityManager->getRepository(Comment::class)->find($id);

        return $this->render('back/comment/show.html.twig', [
           'comment' => $comment,
        ]);
    }

    /**
     * @Route("/admin/commentaires/desactiver/{id}", name="back_comments_disable")
     * @param EntityManagerInterface $entityManager
     * @param $id
     * @IsGranted("ROLE_ADMIN")
     */
    public function disable(EntityManagerInterface $entityManager,$id)
    {
        $comment = $entityManager->getRepository(Comment::class)->find($id);
        $comment->setActive(!$comment->isActive());
        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirectToRoute('back_comments_list');
    }
}
