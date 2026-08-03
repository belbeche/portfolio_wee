<?php

namespace App\Controller\Back;

use App\Entity\Prospect;
use App\Entity\ProspectNote;
use App\Form\ProspectNoteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Journal des echanges avec un prospect.
 *
 * @Route("/admin/prospects")
 */
class ProspectNoteController extends AbstractController
{
    /**
     * Fiche du prospect : ses informations, son historique, et le
     * formulaire d'ajout d'un echange.
     *
     * @Route("/{id}/journal", name="back_prospect_notes", methods={"GET","POST"})
     */
    public function journal(Prospect $prospect, Request $request, EntityManagerInterface $em): Response
    {
        $note = new ProspectNote();
        $form = $this->createForm(ProspectNoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setProspect($prospect);

            $user = $this->getUser();
            $note->setAuthor($user ? $user->getUserIdentifier() : null);

            // Un echange enregistre vaut contact : on avance le statut et on
            // programme la relance suivante, sans avoir a y penser.
            if (in_array($note->getType(), ['appel', 'email'], true)) {
                $prospect->registerContact($note->getCreatedAt());
            } elseif ('rdv' === $note->getType()) {
                $prospect->setStatus(Prospect::STATUS_MEETING);
            } elseif ('proposition' === $note->getType()) {
                $prospect->setStatus(Prospect::STATUS_PROPOSAL);
            } elseif ('refus' === $note->getType()) {
                $prospect->setStatus(Prospect::STATUS_LOST);
                $prospect->setNextFollowUpAt(null);
            }

            $em->persist($note);
            $em->flush();

            $this->addFlash('success', 'Échange enregistré.');

            return $this->redirectToRoute('back_prospect_notes', ['id' => $prospect->getId()]);
        }

        return $this->render('back/prospect/journal.html.twig', [
            'prospect' => $prospect,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/note/{id}/supprimer", name="back_prospect_note_delete", methods={"POST"})
     */
    public function delete(ProspectNote $note, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_note_'.$note->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $prospectId = $note->getProspect() ? $note->getProspect()->getId() : null;

        $em->remove($note);
        $em->flush();

        $this->addFlash('success', 'Échange supprimé.');

        return $prospectId
            ? $this->redirectToRoute('back_prospect_notes', ['id' => $prospectId])
            : $this->redirectToRoute('back_prospect_index');
    }
}
