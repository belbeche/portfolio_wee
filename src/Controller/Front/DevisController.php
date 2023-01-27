<?php

namespace App\Controller\Front;

use Exception;
use App\Entity\User;
use App\Entity\Devis;
use App\Form\UserType;
use App\Form\DevisType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class DevisController extends AbstractController
{
    /**
     * @Route("/devis-en-ligne/{email}", name="front_devis")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, User $user): Response
    {
        $devis = new Devis();

        $form = $this->createForm(DevisType::class, $devis);


        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                if (!$devis) {
                    throw new Exception('Un devis existe déjà pour ce compte');
                } else {
                    $user->setDevis($devis);

                    $entityManager->persist($devis);
                    $entityManager->flush();

                    return $this->redirectToRoute('app_login');
                }
            }
        }

        return $this->render('front/devis/index.html.twig', [
            'formDevis' => $form->createView(),
        ]);
    }

    /**
     * @Route("/devis/tableau-de-bord/{email}", name="front_request_home")
     * @ParamConverter("email", class="Devis", options={"email": "email"})
     */

    public function request(User $user, EntityManagerInterface $entityManager): Response
    {
        $singleDevis = $entityManager->getRepository(Devis::class)->find($user);




        return $this->render('front/devis/allDevis.html.twig', [
            'singleRequest' => $singleDevis,
        ]);
    }
}
