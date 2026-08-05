<?php

// src/Controller/BlogController.php
namespace App\Controller\Front;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(PostRepository $postRepository, CategoryRepository $categoryRepository): Response
    {
        $category = null;
        $posts = $postRepository->findValidatedPosts();
        $categories = $categoryRepository->findAll(); // Toutes les catégories

        return $this->render('front/blog/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
            'currentCategory' => $categoryRepository->findOneBy(['name' => $category]),
        ]);
    }

    #[Route('/category/{category}', name: 'category')]
    public function category(
        string $category,
        CategoryRepository $categoryRepository,
        PostRepository $postRepository
    ): Response {
        $categoryEntity = $categoryRepository->findOneBy(['name' => $category]);

        if (!$categoryEntity) {
            throw $this->createNotFoundException('La catégorie demandée est introuvable.');
        }
        
        $posts = $postRepository->findValidatedPosts(['category' => $categoryEntity]);

        return $this->render('front/blog/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'posts' => $posts,
            'currentCategory' => $categoryEntity->getName(),
        ]);
    }


    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(Post $post, PostRepository $postRepository): Response
    {
        // Trois lectures suivantes : un article ne doit jamais etre un cul-de-sac.
        $suivants = array_values(array_filter(
            $postRepository->findValidatedPosts(),
            static fn (Post $p) => $p->getId() !== $post->getId()
        ));

        return $this->render('front/blog/show.html.twig', [
            'post' => $post,
            'suivants' => array_slice($suivants, 0, 3),
        ]);
    }
}
