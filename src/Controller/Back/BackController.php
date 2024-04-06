<?php

namespace App\Controller\Back;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackController extends AbstractController
{
    /**
     * @Route("/admin", name="back_home")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function home(
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator
    ): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findBy([],['createdAt' => 'DESC']);

        $data = $articles;

        $articles = $paginator->paginate(
            $data,
            $request->query->getInt('page',1),
            12
        );



        return $this->render('back/home.html.twig',[
            'articles' => $articles
        ]);
    }
}
