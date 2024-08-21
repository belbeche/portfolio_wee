<?php

namespace App\Controller\Back;

use App\Entity\Prospect;
use App\Form\ProspectType;
use Symfony\Component\Mime\Email;
use App\Repository\ProspectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProspectController extends AbstractController
{
    /**
     * @Route("/admin/prospects", name="back_prospect_index", methods={"GET"})
     */
    public function index(ProspectRepository $prospectRepository): Response
    {
        return $this->render('back/prospect/index.html.twig', [
            'prospects' => $prospectRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/prospect/new", name="back_prospect_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $prospect = new Prospect();
        $form = $this->createForm(ProspectType::class, $prospect);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentFile = $form->get('document')->getData();
            if ($documentFile) {
                $newFilename = uniqid().'.'.$documentFile->guessExtension();
                try {
                    $documentFile->move($this->getParameter('prospects_directory'), $newFilename);
                    $prospect->setDocumentFilename($newFilename);
                } catch (FileException $e) {
                    $e->getMessage();
                }
            }

            $entityManager->persist($prospect);
            $entityManager->flush();

            // Envoi d'un email de bienvenue au prospect
            $email = (new Email())
                ->from('contact@scriptzenit.fr')
                ->to($prospect->getEmail())
                ->bcc('wbelbeche.s@gmail.com')
                ->subject('Suite conversation téléphonique - ScriptzenIT')
                ->html($this->renderView('back/prospect/welcome_prospect.html.twig', [
                    'prospect' => $prospect
                ]))
                ->attachFromPath($this->getParameter('prospects_directory').'/'.$prospect->getDocumentFilename());


            $mailer->send($email);

            return $this->redirectToRoute('back_prospect_index');
        }

        return $this->render('back/prospect/new.html.twig', [
            'prospect' => $prospect,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/prospects/{id}/edit", name="back_prospect_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Prospect $prospect, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ProspectType::class, $prospect);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Envoi d'un email de notification
            $email = (new Email())
                ->from('contact@scriptzenit.fr')
                ->to($prospect->getEmail())
                ->subject('Prospect Modifié')
                ->text('Le prospect avec l\'email: ' . $prospect->getEmail() . ' a été modifié.');

            $mailer->send($email);

            return $this->redirectToRoute('back_prospect_index');
        }

        return $this->render('back/prospect/edit.html.twig', [
            'prospect' => $prospect,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/prospects/{id}", name="back_prospect_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Prospect $prospect, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$prospect->getId(), $request->request->get('_token'))) {

            // Envoi d'un email de notification
            $email = (new Email())
                ->from('contact@scriptzenit.fr')
                ->to($prospect->getEmail())
                ->subject('Prospect supprimé')
                ->text('Le prospect avec l\'email: ' . $prospect->getEmail() . ' a été supprimé.');

            $mailer->send($email);

            $entityManager->remove($prospect);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_prospect_index');
    }
}