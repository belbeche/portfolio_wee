<?php

namespace App\Controller\Back;

use App\Entity\Subject;
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
     * @Route("/admin/support", name="back_home_support")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function home(
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator
    ): Response
    {
        $Subjects = $entityManager->getRepository(Subject::class)->findBy([],['createdAt' => 'DESC']);

        $data = $Subjects;

        $Subjects = $paginator->paginate(
            $data,
            $request->query->getInt('page',1),
            12
        );



        return $this->render('back/home.html.twig',[
            'Subjects' => $Subjects
        ]);
    }
}
