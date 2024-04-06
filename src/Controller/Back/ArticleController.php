<?php

namespace App\Controller\Back;

use App\Entity\Article;
use App\Entity\Image;
use App\Entity\User;
use App\Form\Article\Type\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Component\Pager\PaginatorInterface;


class ArticleController extends AbstractController
{

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return mixed
     * @Route("/admin/articles", name="back_articles_list")
     * @IsGranted("ROLE_ADMIN")
     */
    public function list(
        Request $request,
        PaginatorInterface $paginator
    ) {

        $data = $this->entityManager->getRepository(Article::class)->findAll();

        $articles = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('back/article/list.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/admin/articles/new", name="back_articles_new")
     *
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(
        Request $request
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


                $this->entityManager->persist($article);

                $this->entityManager->flush();

                $this->addFlash('success', 'Article ajouté avec succéss');

                return $this->redirectToRoute('back_home');
            }
        }

        return $this->render('back/article/new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    /**
     * @Route("/publier/{slug}", name="back_articles_publish")
     */
    public function publish(Article $article)
    {
        if (
            $article->getImages() !== null && $article->getImages() !== ''
            && $article->getContent() !== null && $article->getContent() !== ''
            && $article->getCategories() !== null
            && $article->getTitle() !== null && $article->getTitle() !== ''
        ) {
            $article->setPublishedAt(new \DateTime);

            $article->setIsPublished(true);

            $this->addFlash('success', 'Article publié avec succéss');

            $this->entityManager->persist($article);
            $this->entityManager->flush();

            // We redirect to the article display page if everything is OK
            return $this->redirectToRoute(
                'back_articles_show',
                ['slug' => $article->getSlug()]
            );
        }

        return $this->redirectToRoute(
            'back_articles_edit',
            ['slug' => $article->getSlug()]
        );
    }

    /**
     * @Route("/admin/articles/show/{slug}", name="back_articles_show")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(
        Article $article,
        EntityManagerInterface $entityManager
    ): Response {
        $article = $entityManager->getRepository(Article::class)->find($article);

        return $this->render('back/article/show.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * @Route("/admin/articles/edit/{slug}", name="back_articles_edit")
     * @param Request $request
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(
        Article $article,
        Request $request
    ): Response {
        $form = $this->createForm(ArticleType::class, $article);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $article->setUpdatedAt(new \DateTime("NOW"));

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
                    $article->addImage($img);
                    $img->setArticle($article);
                }

                /*if ($article->getPublishedAt() === null) {
                    // on renseigne le slug de l'article
                    $article->setSlug(
                        strtolower($article->getSlug())
                    );
                }*/


                $this->entityManager->persist($article);
                $this->entityManager->flush();

                return $this->redirectToRoute('back_home');
            }
        }

        return $this->render('back/article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }


    /**
     * @Route("/admin/articles/disable/{id}", name="back_articles_disable")
     * @param $id
     * @return mixed
     * @IsGranted("ROLE_ADMIN")
     */
    public function disable(
        $id
    ): Response {
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        $article->setActive(!$article->isActive());
        $this->entityManager->persist($article);
        $this->entityManager->flush();
        if ($article->isActive() == true) {
            $this->addFlash('warning', "L'article " . $article->getSlug() . " a bien était désactiver :[");
        } else {
            $this->addFlash('success', "L'article " . $article->getSlug() . " a bien était réactiver :]");
        }

        return $this->redirectToRoute('back_articles_list');
    }

    /**
     * @Route("/admin/article/remove/{slug}", name="back_articles_remove")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function remove(
        Article $article,
        Request $request
    ): Response {

        if ($article->getCreatedAt(true)) {
            $this->addFlash('success', "L'article " . $article->getSlug() . " est supprimé avec success !");
            $this->entityManager->remove($article);
            $this->entityManager->flush();
        } else {
            $this->addFlash('success', "L'article " . $article->getSlug() . " est activé, disactivez-le puis réessayer, merci.");
        }

        return $this->redirectToRoute('back_home');
    }

    /**
     * @Route("/back/articles/remove/image/{name}", name="back_articles_remove_image", methods={"DELETE"})
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
