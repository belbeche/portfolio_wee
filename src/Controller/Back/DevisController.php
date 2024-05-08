<?php

namespace App\Controller\Back;

use App\Entity\Devis;
use App\Form\DevisType;
use App\Repository\DevisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DevisController extends AbstractController
{
    /**
     * @Route("/admin/devis", name="back_devis_index")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(DevisRepository $devisRepository): Response
    {
        $devis = $devisRepository->findAll();

        return $this->render('back/devis/index.html.twig', [
            'devis' => $devis,
        ]);
    }

    /**
     * @Route("/admin/devis/{id}", name="back_devis_show")
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Devis $devis): Response
    {
        return $this->render('back/devis/show.html.twig', [
            'devis' => $devis,
        ]);
    }

    /**
     * @Route("/admin/devis/{id}/edit", name="back_devis_edit")
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, Devis $devis): Response
    {

        $form = $this->createForm(DevisType::class, $devis);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('back_devis_show', ['id' => $devis->getId()]);
        }

        return $this->render('back/devis/edit.html.twig', [
            'formDevis' => $form->createView(),
            'devis' => $devis,
        ]);
    }

    /**
     * @Route("/admin/devis/{id}/delete", name="back_devis_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, Devis $devis): Response
    {
        $entityManager->remove($devis);
        $entityManager->flush();

        return $this->redirectToRoute('back_devis_index');
    }
}
