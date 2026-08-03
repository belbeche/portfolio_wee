<?php

namespace App\Controller\Back;

use App\Entity\Prospect;
use App\Form\ProspectType;
use Symfony\Component\Mime\Email;
use App\Repository\ProspectRepository;
use App\Service\ProspectOutreach;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProspectController extends AbstractController
{
    /**
     * @Route("/admin/prospects", name="back_prospect_index", methods={"GET"})
     */
    public function index(ProspectRepository $prospectRepository, ProspectOutreach $outreach): Response
    {
        return $this->render('back/prospect/index.html.twig', [
            'prospects' => $prospectRepository->findAll(),
            'waveCandidates' => $outreach->firstContactCandidates(),
            'dueFollowUps' => $outreach->dueFollowUps(),
            'waveMax' => ProspectOutreach::MAX_PAR_VAGUE,
        ]);
    }

    /**
     * Envoie la vague de premiers contacts depuis le back office.
     * Memes garde-fous que la commande : 15 maximum, adresses verifiees
     * uniquement, journal alimente, cadence de relance posee.
     *
     * @Route("/admin/prospects/envoyer-vague", name="back_prospect_send_wave", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function sendWave(Request $request, ProspectOutreach $outreach): Response
    {
        if (!$this->isCsrfTokenValid('prospect_send_wave', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expiree, reessaie.');

            return $this->redirectToRoute('back_prospect_index');
        }

        $resultat = $outreach->sendWave($outreach->firstContactCandidates());
        $this->flashWaveResult($resultat, 'premier(s) contact(s)');

        return $this->redirectToRoute('back_prospect_index');
    }

    /**
     * Envoie les relances dues (cadence J+4 puis J+10) depuis le back office.
     *
     * @Route("/admin/prospects/envoyer-relances", name="back_prospect_send_followups", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function sendFollowUps(Request $request, ProspectOutreach $outreach): Response
    {
        if (!$this->isCsrfTokenValid('prospect_send_followups', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expiree, reessaie.');

            return $this->redirectToRoute('back_prospect_index');
        }

        $resultat = $outreach->sendWave($outreach->dueFollowUps(), true);
        $this->flashWaveResult($resultat, 'relance(s)');

        return $this->redirectToRoute('back_prospect_index');
    }

    /**
     * Envoi individuel : premier contact si le prospect est "A contacter",
     * relance sinon. Accessible depuis le journal du prospect.
     *
     * @Route("/admin/prospects/{id}/envoyer", name="back_prospect_send_one", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function sendOne(Request $request, Prospect $prospect, ProspectOutreach $outreach, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('prospect_send_one_'.$prospect->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expiree, reessaie.');

            return $this->redirectToRoute('back_prospect_notes', ['id' => $prospect->getId()]);
        }

        $relance = Prospect::STATUS_TO_CONTACT !== $prospect->getStatus();
        $erreur = $outreach->send($prospect, $relance);

        if (null === $erreur) {
            $entityManager->flush();
            $this->addFlash('success', sprintf('%s envoye a %s. Journal mis a jour, relance posee automatiquement.',
                $relance ? 'Relance envoyee' : 'Premier contact envoye', $prospect->getEmail()));
        } else {
            $this->addFlash('error', sprintf('Envoi impossible : %s', $erreur));
        }

        return $this->redirectToRoute('back_prospect_notes', ['id' => $prospect->getId()]);
    }

    /** @param array{sent: int, errors: array<string, string>} $resultat */
    private function flashWaveResult(array $resultat, string $libelle): void
    {
        if ($resultat['sent'] > 0) {
            $this->addFlash('success', sprintf('%d %s envoye(s). Journal mis a jour, cadence de relance posee (J+4 puis J+10).',
                $resultat['sent'], $libelle));
        }
        if ([] !== $resultat['errors']) {
            foreach ($resultat['errors'] as $societe => $message) {
                $this->addFlash('error', sprintf('%s : %s', $societe, $message));
            }
        }
        if (0 === $resultat['sent'] && [] === $resultat['errors']) {
            $this->addFlash('info', 'Rien a envoyer : aucun candidat avec une adresse verifiee.');
        }
    }
    
   /**
     * @Route("/admin/prospect/new", name="back_prospect_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $prospect = new Prospect();
        $form = $this->createForm(ProspectType::class, $prospect);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentFiles = $form->get('document')->getData();
            $filenames = [];

            if ($documentFiles) {
                foreach ($documentFiles as $documentFile) {
                    if ($documentFile instanceof UploadedFile) {
                        $newFilename = uniqid() . '.' . $documentFile->guessExtension();
                        try {
                            $documentFile->move($this->getParameter('prospects_directory'), $newFilename);
                            $filenames[] = $newFilename;
                        } catch (FileException $e) {
                            // Handle file upload exception
                            $this->addFlash('error', 'Error uploading file: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Convert filenames array to comma-separated string
            $filenamesString = implode(', ', $filenames);
            $prospect->setDocumentFilenames($filenamesString); 
            
            $entityManager->persist($prospect);
            $entityManager->flush();

            // Message personnalise : il remplace le paragraphe generique de
            // l'e-mail et il est archive dans le journal du prospect.
            $customMessage = trim((string) $form->get('message')->getData());

            if ('' !== $customMessage) {
                $note = new \App\Entity\ProspectNote();
                $note->setProspect($prospect)
                    ->setType('email')
                    ->setContent($customMessage);
                $entityManager->persist($note);
                $prospect->registerContact();
                $entityManager->flush();
            }

            // Prepare email
            $email = (new Email())
                ->from('contact@walidbelbeche.fr')
                ->to($prospect->getEmail())
                ->bcc('wbelbeche.s@gmail.com')
                ->subject('Suite conversation téléphonique - Walid BELBECHE.fr')
                ->html($this->renderView('back/prospect/welcome_prospect.html.twig', [
                    'prospect' => $prospect,
                    'customMessage' => $customMessage,
                ]));

            // Attach files if any
            foreach ($filenames as $filename) {
                $email->attachFromPath($this->getParameter('prospects_directory') . '/' . $filename);
            }

            // L'offre une page accompagne systematiquement le premier contact.
            $offre = $this->getParameter('kernel.project_dir').'/public/docs/offre-walid-belbeche.pdf';
            if (is_file($offre)) {
                $email->attachFromPath($offre, 'Offre - Walid Belbeche.pdf');
            }

            try {
                $mailer->send($email);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error sending email: ' . $e->getMessage());
                return $this->redirectToRoute('back_prospect_index');
            }

            return $this->redirectToRoute('back_prospect_index');
        }

        return $this->render('back/prospect/new.html.twig', [
            'prospect' => $prospect,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Envoie l'e-mail prospect a ta propre adresse, avec des donnees
     * d'exemple, pour verifier le rendu avant un vrai envoi.
     *
     * @Route("/admin/prospect/test-email", name="back_prospect_test_email", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function testEmail(Request $request, MailerInterface $mailer): Response
    {
        if (!$this->isCsrfTokenValid('prospect_test_email', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de securite invalide.');

            return $this->redirectToRoute('back_prospect_new');
        }

        $exemple = (new Prospect())
            ->setEmail('wbelbeche.s@gmail.com')
            ->setCompany('Entreprise Exemple')
            ->setContactName('Client de test');

        $message = trim((string) $request->request->get('message'));

        $email = (new Email())
            ->from('contact@walidbelbeche.fr')
            ->to('wbelbeche.s@gmail.com')
            ->subject('[TEST] Suite conversation téléphonique - Walid BELBECHE.fr')
            ->html($this->renderView('back/prospect/welcome_prospect.html.twig', [
                'prospect' => $exemple,
                'customMessage' => $message,
            ]));

        try {
            $mailer->send($email);
            $this->addFlash('success', 'E-mail test envoyé à wbelbeche.s@gmail.com. Regarde ta boîte, spams compris.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Envoi impossible : '.$e->getMessage());
        }

        return $this->redirectToRoute('back_prospect_new');
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
                ->from('contact@walidbelbeche.fr')
                ->to($prospect->getEmail())
                ->subject('Prospect Modifié - Walid BELBECHE.fr')
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
                ->from('contact@walidbelbeche.fr')
                ->to($prospect->getEmail())
                ->subject('Prospect supprimé - Walid BELBECHE.fr')
                ->text('Le prospect avec l\'email : ' . $prospect->getEmail() . ' a bien était supprimé. Merci pour votre temps.');

            $mailer->send($email);

            $entityManager->remove($prospect);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_prospect_index');
    }
}