<?php

namespace App\Controller\Back;

use App\Entity\Project;
use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    /**
     * @Route("/admin/categorie", name="back_category_index")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findAll();
        
        return $this->render('back/category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/admin/categorie/new", name="back_category_new")
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {

        $category = new Category();

        // dd($category);

        $form = $this->createForm(CategoryType::class, $category);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                // Le slug est obligatoire en base mais absent du formulaire :
                // on le derive du titre, en garantissant l'unicite.
                if (!$category->getSlug()) {
                    $base = strtolower($slugger->slug((string) $category->getName())->toString());
                    $slug = $base !== '' ? $base : 'categorie';
                    $i = 2;
                    while ($entityManager->getRepository(Category::class)->findOneBy(['slug' => $slug])) {
                        $slug = $base.'-'.$i++;
                    }
                    $category->setSlug($slug);
                }

                $entityManager->persist($category);
                $entityManager->flush();

                return $this->redirectToRoute('back_category_index');
            }
        }


        return $this->render('back/category/new.html.twig', [
            'categoryForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/categorie/{id}/modifier", name="back_categories_edit", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Categorie mise a jour.');

            return $this->redirectToRoute('back_category_index');
        }

        return $this->render('back/category/edit.html.twig', [
            'categoryForm' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * @Route("/admin/categorie/{id}/supprimer", name="back_categories_remove", methods={"POST"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function remove(Request $request, EntityManagerInterface $entityManager, Category $category): Response
    {
        if (!$this->isCsrfTokenValid('delete_category'.$category->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('back_category_index');
        }

        $liees = count($category->getProjects()) + count($category->getSubjects()) + count($category->getPosts());
        if ($liees > 0) {
            $this->addFlash('warning', sprintf(
                'Impossible de supprimer "%s" : %d contenu(s) y sont encore rattaches. Reclassez-les d\'abord.',
                $category->getName(), $liees
            ));

            return $this->redirectToRoute('back_category_index');
        }

        $entityManager->remove($category);
        $entityManager->flush();
        $this->addFlash('success', 'Categorie supprimee.');

        return $this->redirectToRoute('back_category_index');
    }

    /**
     * @Route("/admin/categorie/afficher/{id}", name="back_category_show")
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(EntityManagerInterface $entityManager,$id)
    {

        $category = $entityManager->getRepository(Category::class)->find($id);

        return $this->render('back/category/show.html.twig', [
            'category' => $category,
        ]);
    }
}
