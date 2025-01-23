<?php

namespace App\Controller\Back;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/blog', name: 'admin_blog_')]
class BlogAdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();

        return $this->render('back/blog_admin/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleMediaSections($post, $slugger);

            $this->entityManager->persist($post);
            $this->entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès.');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('back/blog_admin/blog_form.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleMediaSections($post, $slugger);

            $this->entityManager->flush();

            $this->addFlash('success', 'Article modifié avec succès.');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('back/blog_admin/blog_form.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($post);
            $this->entityManager->flush();

            $this->addFlash('success', 'Article supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_blog_index');
    }

    private function handleMediaSections(Post $post, SluggerInterface $slugger): void
    {
        foreach ($post->getSections() as $section) {
            if ($section->getType() === 'media' && $section->getMediaFile()) {
                $file = $section->getMediaFile();
                $fileName = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('media_directory'), // Répertoire défini dans les paramètres
                        $fileName
                    );
                    $section->setMediaUrl('uploads/media/' . $fileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du fichier.');
                }
            }
        }
    }

    #[Route('/validate/{id}', name: 'validate', methods: ['POST'])]
    public function validate(Post $post): Response
    {
        $post->setStatus('validated');
        $this->entityManager->flush();

        $this->addFlash('success', 'Article validé avec succès.');
        return $this->redirectToRoute('admin_blog_index');
    }
}
