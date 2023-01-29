<?php

namespace App\Controller\Front;

use Exception;
use App\Entity\User;
use App\Entity\Devis;
use App\Form\DevisType;
use App\Repository\UserRepository;
use App\Repository\DevisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class DevisController extends AbstractController
{
    /**
     * @Route("/devis-en-ligne/{id}", name="front_devis")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $devis = new Devis();

        $form = $this->createForm(DevisType::class, $devis);


        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $devis->setDevisUser($user);

                $entityManager->persist($devis);
                $entityManager->flush($devis);

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('front/devis/index.html.twig', [
            'formDevis' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tableau-de-bord/{id}", name="front_board")
     *
     * @param DevisRepository $devisRepo
     * @return void
     */
    public function Show($id, EntityManagerInterface $entityManager): Response
    {

        dd($id);
        $userDevis = $entityManager->getRepository(User::class)->find($id);

        return $this->render('devis/allDevis.html.twig', [
            'singleDevis' => $userDevis,
        ]);
    }
}
