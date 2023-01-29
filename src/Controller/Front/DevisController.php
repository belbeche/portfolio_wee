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
                $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $request->get('id')]);
                $devis->setUser($user);
                $entityManager->persist($devis);
                $entityManager->flush($devis);

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('front/devis/new.html.twig', [
            'formDevis' => $form->createView(),
        ]);
    }

    /**
     * @Route("/devis/utilisateur", name="front_devis_utilisateur_show")
     * 
     * @param DevisRepository $devisRepo
     * @return Response
     */
    public function Show(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $entityManager->getRepository(Devis::class)->findOneBy(['user' => $this->getUser()]);

        return $this->render('front/devis/show.html.twig', [
            'singleDevis' => $user,
        ]);
    }
}
