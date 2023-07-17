<?php

namespace App\Controller\Back;

use App\Entity\Project;
use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $category = new Category();

        // dd($category);

        $form = $this->createForm(CategoryType::class, $category);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                
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
