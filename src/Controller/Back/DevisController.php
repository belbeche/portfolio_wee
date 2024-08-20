<?php

namespace App\Controller\Back;

use Mpdf\Mpdf;
use Knp\Snappy\Pdf;
use App\Entity\User;
use App\Entity\Devis;
use App\Form\DevisType;
use App\Form\ReplyDevisFormType;
use App\Repository\DevisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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

    /**
     * @Route("/admin/devis/{id}/reply", name="back_devis_reply")
     * @IsGranted("ROLE_ADMIN")
     */
    public function reply(Request $request, MailerInterface $mailer, Devis $devis, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReplyDevisFormType::class, $devis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour du devis
            $devis->setResponse($form->get('response')->getData());
            $devis->setPrix($form->get('prix')->getData());
            $devis->setStatut('Envoyé');
            $entityManager->flush();

            // Générer le PDF du devis
            $html = $this->renderView('front/devis/show.html.twig', [
                'devis' => $devis
            ]);

            $filename = 'devis_' . $devis->getId() . '.pdf';

            $mpdf = new Mpdf();
            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S'); // Générer le PDF en tant que chaîne

            // Envoi de l'e-mail avec le récapitulatif du devis et le PDF en pièce jointe
            $email = (new TemplatedEmail())
            ->from('contact@scriptzenit.fr')
            ->to($devis->getEmail())
            ->bcc('wbelbeche.s@gmail.com')
            ->subject('Réponse à votre demande de devis #' . $devis->getId())
            ->htmlTemplate('back/devis/email_reply.html.twig')
            ->context([
                'client' => $devis->getUser(),
                'response' => $devis->getResponse(),
                'prix' => $devis->getPrix(),
                'devis' => $devis
            ])
            ->attach($pdfContent, 'devis_' . $devis->getId() . '.pdf', 'application/pdf');

            // Envoyer l'email
            $mailer->send($email);

            $this->addFlash('success', 'La réponse au devis a été envoyée avec succès.');
            return $this->redirectToRoute('back_devis_index');
        }

        return $this->render('back/devis/reply_devis.html.twig', [
            'form' => $form->createView(),
            'devis' => $devis
        ]);
    }

}
