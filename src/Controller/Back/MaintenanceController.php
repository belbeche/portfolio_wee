<?php

namespace App\Controller\Back;

use App\Service\Sauvegarde;
use App\Service\Settings;
use App\Service\Surveillance;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * L'etat de sante du site, au meme endroit que les sauvegardes.
 *
 * @Route("/admin/maintenance")
 * @IsGranted("ROLE_ADMIN")
 */
class MaintenanceController extends AbstractController
{
    /**
     * @Route("", name="back_maintenance", methods={"GET"})
     */
    public function index(Surveillance $surveillance, Sauvegarde $sauvegarde, Settings $settings): Response
    {
        $dernierControle = (int) $settings->get('surveillance_dernier_passage', '0');

        return $this->render('back/maintenance/index.html.twig', [
            // On controle a l'affichage : la page dit l'etat maintenant, pas
            // celui du dernier passage automatique.
            'controles' => $surveillance->controler(),
            'archives' => $sauvegarde->archives(),
            'aConserver' => Sauvegarde::A_CONSERVER,
            'dernierControle' => $dernierControle > 0 ? (new \DateTime())->setTimestamp($dernierControle) : null,
        ]);
    }

    /**
     * @Route("/sauvegarder", name="back_maintenance_backup", methods={"POST"})
     */
    public function sauvegarder(Request $request, Sauvegarde $sauvegarde, Settings $settings): Response
    {
        if (!$this->isCsrfTokenValid('maintenance_backup', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expiree, reessaie.');

            return $this->redirectToRoute('back_maintenance');
        }

        try {
            @set_time_limit(300);
            $chemin = $sauvegarde->creer();
            $settings->set('sauvegarde_dernier_passage', (string) time());

            $this->addFlash('success', sprintf(
                'Sauvegarde %s creee (%.1f Mo). Les %d plus recentes sont conservees, les autres sont effacees automatiquement.',
                basename($chemin),
                filesize($chemin) / 1048576,
                Sauvegarde::A_CONSERVER
            ));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Sauvegarde impossible : '.$e->getMessage());
        }

        return $this->redirectToRoute('back_maintenance');
    }

    /**
     * @Route("/controler", name="back_maintenance_check", methods={"POST"})
     */
    public function controler(Request $request, Surveillance $surveillance): Response
    {
        if (!$this->isCsrfTokenValid('maintenance_check', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expiree, reessaie.');

            return $this->redirectToRoute('back_maintenance');
        }

        $bilan = $surveillance->surveiller(true);
        $this->addFlash('success', 'Controle effectue et rapport envoye : '.$bilan['message']);

        return $this->redirectToRoute('back_maintenance');
    }

    /**
     * Telechargement d'une archive.
     *
     * Le nom recu est reduit a son nom de fichier et doit correspondre au
     * motif attendu : sans cela, un ../../.env suffirait a repartir avec les
     * identifiants du site.
     *
     * @Route("/archive/{nom}", name="back_maintenance_download", methods={"GET"}, requirements={"nom"="[\\w\\.\\-]+"})
     */
    public function telecharger(string $nom, Sauvegarde $sauvegarde): Response
    {
        $nom = basename($nom);
        if (!preg_match('/^sauvegarde-[0-9_\-]+\.zip$/', $nom)) {
            throw $this->createNotFoundException('Archive inconnue.');
        }

        $chemin = $sauvegarde->dossier().'/'.$nom;
        if (!is_file($chemin)) {
            throw $this->createNotFoundException('Archive introuvable.');
        }

        $reponse = new BinaryFileResponse($chemin);
        $reponse->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $nom);

        return $reponse;
    }

    /**
     * @Route("/archive/{nom}/supprimer", name="back_maintenance_delete", methods={"POST"}, requirements={"nom"="[\\w\\.\\-]+"})
     */
    public function supprimer(string $nom, Request $request, Sauvegarde $sauvegarde): Response
    {
        $nom = basename($nom);

        if (!$this->isCsrfTokenValid('maintenance_delete_'.$nom, (string) $request->request->get('_token'))
            || !preg_match('/^sauvegarde-[0-9_\-]+\.zip$/', $nom)) {
            $this->addFlash('error', 'Suppression refusee.');

            return $this->redirectToRoute('back_maintenance');
        }

        $chemin = $sauvegarde->dossier().'/'.$nom;
        if (is_file($chemin) && @unlink($chemin)) {
            $this->addFlash('success', $nom.' supprimee.');
        } else {
            $this->addFlash('error', 'Impossible de supprimer '.$nom);
        }

        return $this->redirectToRoute('back_maintenance');
    }
}
