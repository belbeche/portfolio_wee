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
     * @Route("/admin/devis/{id}/delete", name="back_devis_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, Devis $devis): Response
    {
        if ($this->isCsrfTokenValid('delete_devis'.$devis->getId(), $request->request->get('_token'))) {
            $entityManager->remove($devis);
            $entityManager->flush();
            $this->addFlash('success', 'Devis supprime.');
        }

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

            // Le devis en PDF. On rend un gabarit dedie a mPDF et non la page
            // web : une page concue pour un navigateur donne un PDF decale,
            // avec les images en carre rouge et les tableaux qui debordent.
            $html = $this->renderView('front/devis/devis_pdf.html.twig', [
                'devis' => $devis,
            ]);

            $reference = 'DEV-'.$devis->getCreatedAt()->format('Y').'-'
                .strtoupper(substr((string) $devis->getId(), 0, 6));
            $filename = 'Devis-'.$reference.'.pdf';

        // mPDF ne devine pas le format : sans ces reglages il compose en
        // Lettre americaine avec des marges par defaut, et le gabarit prevu
        // pour du A4 se retrouve decale.
        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 12,
            'margin_bottom' => 20,
            'margin_left' => 12,
            'margin_right' => 12,
            'default_font' => 'dejavusans',
        ]);
            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');

            // Envoi de l'e-mail avec le récapitulatif du devis et le PDF en pièce jointe
            $email = (new TemplatedEmail())
            ->from('contact@walidbelbeche.fr')
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
            ->attach($pdfContent, $filename, 'application/pdf');

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
