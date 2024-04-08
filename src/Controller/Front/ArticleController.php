<?php
// app/Controller/Front;

namespace App\Controller\Front;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Image;
use App\Form\Article\Type\ArticleType;
use App\Form\Article\Type\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ArticleController extends AbstractController
{

    /**
     * @Route("/profil/assistance/article/nouveau", name="front_articles_new")
     *
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_USER")
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $article->setUser($this->getUser());

                $article->setCreatedAt(new \DateTime);

                $article->setIsPublished(false);

                $images = $form->get('images')->getData();

                foreach ($images as $image) {
                    // We generate a new file name
                    $file = md5(uniqid()) . '.' . $image->guessExtension();

                    // On copie le fichier dans le dossier uploads

                    $image->move(
                        $this->getParameter('images_directory'),
                        $file
                    );

                    // We add image in the database and to the article
                    $img = new Image();
                    $img->setName($file);
                    $article->addImage($img);
                    $img->setArticle($article);
                }

                $entityManager->persist($article);

                $entityManager->flush();

                $this->addFlash('success', 'Demande en cours de traitement ajouté en brouillon, en attente de validation.');

                return $this->redirectToRoute('front_articles_show', ['title' => $article->getTitle()]);
            }
        }

        return $this->render('front/subject/new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    /**
     * @Route("/articles/{title}", name="front_articles_show")
     * @return Response
     */
    public function show(
        EntityManagerInterface $entityManager,
        Request $request,
        Article $article
    ): Response {
        // to get the comment in the article, we assign it in the variable $article
        $article = $entityManager->getRepository(Article::class)->find($article->getId());

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $comment->setArticle($article);
                $comment->setUser($this->getUser());
                /*dump($this->getUser());die();*/
                $entityManager->persist($comment);
                $entityManager->flush();

                return $this->redirectToRoute('front_articles_show', ['title' => $article->getTitle()]);
            }
        }

        $comments = $entityManager->getRepository(Comment::class)->findBy(
            [
                'article' => $article,
                'active' => true,
            ],
            ['id' => 'DESC']
        );

        return $this->render('front/subject/show.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
            'comments' => $comments
        ]);
    }
}
