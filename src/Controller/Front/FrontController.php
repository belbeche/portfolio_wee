<?php

namespace App\Controller\Front;

use App\Data\SearchData;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\UserLike;
use App\Form\Model\SearchModel;
use App\Form\Search\SearchType;
use App\Repository\ArticleRepository;
use App\Service\CurlService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Amp\Promise\all;

use App\Form\EditProfileType;
use App\Form\User\Type\UserFormType;
use phpDocumentor\Reflection\Types\Mixed_;



class FrontController extends AbstractController
{
    /**
     * @Route("/support", name="front_support")
     */
    public function home(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator, ArticleRepository $articleRepository)
    {

        $data = $entityManager->getRepository(Article::class)->findBy(['isPublished' => true], ['id' => 'desc']);

        $users = $entityManager->getRepository(User::class)->findAll();

        $articles = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            4
        );

        $categories = $entityManager->getRepository(Category::class)->findAll();

        /*$articles = $articleRepository->findSearch($search,$paginator);*/

        /*if($request->isMethod('POST'))
        {
            if($formSearch->isSubmitted() && $formSearch->isValid())
            {
                $entityManager->persist($users);
                $entityManager->persist($search);
                $entityManager->flush();

                $this->addFlash('success', 'Merci de votre inscription ! Vous serez informé sous peu de nos dernières actualités.');
                return $this->redirectToRoute('front_home');
            }
        }*/

        // session_start();

        // dd(unserialize($_SESSION['_sf2_attributes']['_security_main']));

        return $this->render('front/home.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
            'users' => $users
        ]);
    }

    /**
     * @Route("/contact", name="front_contact")
     * @return Response
     */
    public function contact()
    {
        return $this->render('front/contact/contact.html.twig');
    }

    /**
     * @Route("/rechercher", name="front_search")
     */
    public function search(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator)
    {
        $searchModel = new SearchModel();
        $form = $this->createForm(SearchType::class, $searchModel);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $entityManager->getRepository(Article::class)->findBySearch($searchModel);

                $articles = $paginator->paginate(
                    $data,
                    $request->query->getInt('page', 1),
                    10
                );

                return $this->render('front/result.html.twig', [
                    'articles' => $articles,
                ]);
            }
        }

        return $this->render('front/includes/search.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/blog/categorie/{name}", name="front_category")
     * @return Response
     */
    public function Categories(Category $category, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $category = $entityManager->getRepository(Category::class)->find($category);

        $articles = $entityManager->getRepository(Article::class)->findByCategoryName($category->getName());

        return $this->render('front/category/home.html.twig', [
            'categorie' => $category,
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/blog/likes/{id}", name="front_like")
     * @return Response
     */
    public function isLike(EntityManagerInterface $entityManager, int $id): Response
    {
        // Je recupère l'article sur lequel je clique.
        $article = $entityManager->getRepository(Article::class)->find($id);
        // Je récurpère le userLike en fonction de l'article ci-dessus.
        $userLike = $entityManager->getRepository(UserLike::class)->findOneBy(['article' => $article]);

        $isLike = false;

        // On boucle sur l'existant
        foreach ($article->getLikes() as $like) {
            // l'utilisateur connecté figure dans la liste des userLikes on fonction
            // de l'article courant
            if ($like->getUser() == $this->getUser()) {
                // on pass le flag à true
                $isLike = true;
            }
        }

        // on se base sur le flag afin de déterminer si on remove ou pas.
        if ($isLike) {
            $entityManager->remove($userLike);
        } else {
            // Sinon on ajoute un like
            $userLike = new UserLike();

            $userLike->setArticle($article);
            $userLike->setUser($this->getUser());
            $entityManager->persist($userLike);
        }

        $entityManager->flush();

        //        return $this->redirectToRoute('front_home');
        return new JsonResponse([
            'success' => 'Enregistrement ok !'
        ], 200);
    }

    /**
     * @Route("/mes-favoris/{id}", name="front_favorites" , requirements={"id":"\d+"})
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function favorite(EntityManagerInterface $entityManager, int $id)
    {

        // Je recupère l'article sur lequel je clique.
        $article = $entityManager->getRepository(Article::class)->find($id);

        $userLike = $entityManager->getRepository(UserLike::class)->findOneBy(['article' => $article]);

        $userLike->getLike();

        dd($userLike);

        return $this->render('front/favorites/show.html.twig', [
            'favorites' => $favorites,
        ]);
    }


    /**
     * @Route("/blog", name="front_blog")
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function blog(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator)
    {
        $data = $entityManager->getRepository(Article::class)->findBy(['isPublished' => true], ['id' => 'desc']);

        $articles = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            4
        );

        $categories = $entityManager->getRepository(Category::class)->findAll();

        return $this->render('front/article/blog.html.twig', [
            'articles' => $articles,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/api/resources", name="api_resources")
     * @return Response
     */
    public function getResources(CurlService $curlService): Response
    {
        return new jsonResponse([
            'resources' => $curlService->getResources(
                $this->getParameter('api_resource_url'),
                $this->getParameter('api_resource_token')
            )
        ]);
    }

    /**
     * @Route("/utilisateur/sujet/{id}", name="front_user_subject")
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function mySubject(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator)
    {

        $data = $entityManager->getRepository(Article::class)->findBySubject();

        $articles = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            4
        );

        $categories = $entityManager->getRepository(Category::class)->findAll();

        return $this->render('front/profil/profil_subject.html.twig', [
            'articles' => $articles,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/utilisateur/reponses/{id}", name="front_user_response_subject")
     */
    public function myResponse(EntityManagerInterface $entityManager,$id): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);
        return $this->render('front/profil/user_response_subject.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/utilisateur/supression/{id}", name="front_user_remove")
     */

    public function remove(EntityManagerInterface $entityManager, $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('front_user_subject');
    }
}
