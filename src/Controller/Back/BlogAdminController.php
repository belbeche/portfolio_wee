<?php

namespace App\Controller\Back;

use App\Entity\Post;
use App\Form\PostType;
use App\Entity\PostSection;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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

    /**
     * @Route("/create", name="create", methods={"GET", "POST"})
     */
    public function create(Request $request, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement du contenu principal
            $post->setContent($request->request->get('main_content'));

            // Traitement des sections
            $sectionsData = $request->request->all()['sections'] ?? [];
            foreach ($sectionsData as $index => $sectionData) {
                $section = new PostSection();
                $section->setType($sectionData['type']);
                
                if ($sectionData['type'] === 'media') {
                    $file = $request->files->get('sections')[$index]['mediaFile'] ?? null;
                    if ($file) {
                        $fileName = $slugger->slug(uniqid()) . '.' . $file->guessExtension();
                        $file->move($this->getParameter('media_directory'), $fileName);
                        $section->setMediaUrl($fileName);
                    }
                } else {
                    $section->setContent($sectionData['content']);
                    
                    if ($sectionData['type'] === 'code') {
                        $section->setLanguage($sectionData['language'] ?? 'plaintext');
                    }
                }
                
                $post->addSection($section);
            }

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

    /**
    * @Route("/admin/blog/{id}/edit", name="edit", requirements={"id"="\d+"}, defaults={"id"=null})
    */
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        PostRepository $postRepository,
        int $id
    ): Response {
        $post = $id ? $postRepository->find($id) : new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        // Traitement des sections dynamiques
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données depuis les champs `name="sections[index][type]"`
            $sectionsData = $request->request->all()['sections'] ?? [];

            // Nettoyer les anciennes sections (si édition)
            foreach ($post->getSections() as $existingSection) {
                $post->removeSection($existingSection);
                $em->remove($existingSection);
            }

            // Ajout des nouvelles sections
            foreach ($sectionsData as $sectionIndex => $section) {
                $type = $section['type'];
                $content = $section['content'] ?? null;
                $mediaFile = $request->files->get('sections')[$sectionIndex]['mediaFile'] ?? null;

                $newSection = new PostSection();
                $newSection->setType($type);

                if ($type === PostSection::TYPE_MEDIA && $mediaFile instanceof UploadedFile) {
                    $filename = uniqid().'.'.$mediaFile->guessExtension();
                    $mediaFile->move($this->getParameter('uploads_dir'), $filename);
                    $newSection->setMediaUrl('/uploads/' . $filename);
                } else {
                    $newSection->setContent($content);
                }

                $post->addSection($newSection);
            }

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Article enregistré avec succès.');
            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('back/blog_admin/blog_form.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    /**
     * @Route("/admin/blog/{id}/delete", name="delete", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($post);
            $this->entityManager->flush();

            $this->addFlash('success', 'Article supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_blog_index');
    }
    /**
     * @Route("/admin/blog/{id}/media", name="media", requirements={"id"="\d+"}, methods={"POST"})
     */
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

    /**
     * @Route("/admin/blog/{id}/validate", name="validate", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function validate(Post $post): Response
    {
        $post->setStatus('validated');
        $this->entityManager->flush();

        $this->addFlash('success', 'Article validé avec succès.');
        return $this->redirectToRoute('admin_blog_index');
    }
}
