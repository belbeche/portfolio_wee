<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Entity\Subject;
use App\Entity\CallbackRequest;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BackController extends AbstractController
{
    /**
     * @Route("/admin/callback-requests", name="back_callback_requests")
     */
    public function listCallbackRequests(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Récupérer les paramètres de tri
        $sortField = $request->query->get('sort', 'id'); // par défaut, tri par ID
        $sortOrder = $request->query->get('order', 'asc'); // par défaut, ordre croissant

        // Valider le champ de tri et l'ordre
        $validSortFields = ['id', 'name', 'phone', 'email', 'status'];
        if (!in_array($sortField, $validSortFields)) {
            $sortField = 'id';
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        // Récupérer les demandes de rappel avec tri
        $callbackRequests = $entityManager->getRepository(CallbackRequest::class)
            ->findBy([], [$sortField => $sortOrder]);

        return $this->render('back/callback/callback_requests.html.twig', [
            'callbackRequests' => $callbackRequests,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
        ]);
    }

     /**
     * @Route("/admin/callback-requests/validate/{id}", name="back_callback_request_validate")
     */
    public function validateRequest(CallbackRequest $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $request->setStatus('validated');
        $entityManager->flush();

        $this->addFlash('success', 'Le prospect a été validé.');
        return $this->redirectToRoute('back_callback_requests');
    }

    /**
     * @Route("/admin/callback-requests/cancel/{id}", name="back_callback_request_cancel")
     */
    public function cancelRequest(CallbackRequest $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $request->setStatus('canceled');
        $entityManager->flush();

        $this->addFlash('warning', 'Le prospect a été annulé.');
        return $this->redirectToRoute('back_callback_requests');
    }

    /**
     * @Route("/admin/callback-requests/relaunch/{id}", name="back_callback_request_relaunch")
     */
    public function relaunchRequest(CallbackRequest $request, Request $httpRequest, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($httpRequest->isMethod('POST')) {
            $request->setStatus('relaunched');
            $request->setRelaunchDate(new \DateTime());
            $entityManager->flush();

            // Envoi de l'email de relance
            $email = (new Email())
                ->from('contact@scriptzenit.fr')
                ->to($request->getEmail())
                ->subject('Relance: ScriptzenIT - Demande de rappel')
                ->text('Bonjour, nous avons essayé de vous joindre sans sucèss, suite à votre demande de rappel. Merci de nous contacter pour plus de détails au 07 62 97 26 91.');

            $mailer->send($email);

            $this->addFlash('info', 'Le prospect a été relancé et un email a été envoyé.');
            return $this->redirectToRoute('back_callback_requests');
        }

        return $this->render('back/callback/relaunch_request.html.twig', [
            'request' => $request,
        ]);
    }

    /**
     * @Route("/admin/callback-requests/remove/{id}", name="back_callback_request_remove", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function removeRequest(CallbackRequest $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($request);
        $entityManager->flush();

        $this->addFlash('danger', 'Le prospect a été supprimé avec succès.');

        return $this->redirectToRoute('back_callback_requests');
    }
    
    /**
     * @Route("/admin/support", name="back_home_support")
     * @return Response
     * @IsGranted("ROLE_ADMIN")
     */
    public function home(
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator
    ): Response
    {
        $Subjects = $entityManager->getRepository(Subject::class)->findBy([],['createdAt' => 'DESC']);

        $data = $Subjects;

        $Subjects = $paginator->paginate(
            $data,
            $request->query->getInt('page',1),
            12
        );



        return $this->render('back/home.html.twig',[
            'Subjects' => $Subjects
        ]);
    }
}
