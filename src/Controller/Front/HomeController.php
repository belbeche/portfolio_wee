<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Contact;
use App\Entity\Project;

use App\Form\ContactType;
use App\Entity\CallbackRequest;
use App\Form\CallbackRequestType;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="front_home")
     * @Route("/realisations/{category}", name="front_project_by_category")
     */
    public function index(EntityManagerInterface $entityManager, Request $request, MailerInterface $mailer, string $category = null): Response
    {
        $callbackRequest = new CallbackRequest();
        $form = $this->createForm(CallbackRequestType::class, $callbackRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $callbackRequest->setEmail($form->get('email')->getData());
            $callbackRequest->setName($form->get('name')->getData());
            $callbackRequest->setPhone($form->get('phone')->getData());

            $entityManager->persist($callbackRequest);
            $entityManager->flush();

            // Envoi de l'email
            $email = (new TemplatedEmail())
                ->from(new Address('contact@scriptzenit.fr', 'ScriptzenIT - Demande de rappel'))
                ->to($form->get('email')->getData())
                ->bcc('wbelbeche.s@gmail.com')
                ->subject('Nouvelle demande de rappel')
                ->htmlTemplate('front/home/callback_request.html.twig')
                ->context([
                    'name' => $callbackRequest->getName(),
                    'phone' => $callbackRequest->getPhone(),
                    'user_email' => $callbackRequest->getEmail(),  // Utilisez 'user_email' au lieu de 'email'
                ]);

            $mailer->send($email);


            $this->addFlash('success', 'Votre demande a été enregistrée avec succès. Nous vous rappellerons bientôt.');

            // Rediriger pour éviter la soumission multiple du formulaire
            return $this->redirectToRoute('front_home');
        }

        if ($category) {
            $projects = $entityManager->getRepository(Project::class)->findBy(['category' => $category]);
        } else {
            $projects = $entityManager->getRepository(Project::class)->findAll();
        }

        return $this->render('front/home/index.html.twig', [
            'projects' => $projects,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/a-propos", name="front_about")
     *
     */
    public function about(): Response
    {
        return $this->render('front/about/index.html.twig');
    }

    /**
     * @Route("/contact", name="front_contact")
     */
    public function contact(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager) {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($contact);
                $entityManager->flush();

                $email = (new TemplatedEmail())
                    ->from('wbelbeche.s@gmail.com')
                    ->to($contact->getEmail())
                    ->bcc('wbelbeche.s@gmail.com')
                    ->subject('Prise de contact,ScriptZenIT')
                    ->html($this->renderView('front/contact/email.html.twig', ['contact' => $contact]));

                $mailer->send($email);

                return new JsonResponse(['success' => true, 'message' => 'Votre message a été envoyé avec succès!']);
            } else {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[$error->getOrigin()->getName()] = $error->getMessage();
                }
                return new JsonResponse(['success' => false, 'errors' => $errors]);
            }
        }

        return $this->render('front/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mentions-légales", name="front_mentions")
     */
    public function mentionsLegales(): Response{
        return $this->render('front/administrative/mentions.html.twig');
    }

    /**
     * @Route("/cguv", name="front_cguv")
     */
    public function cguv(): Response{
        return $this->render('front/administrative/cguv.html.twig');
    }

    /**
     * @Route("/conditions-utilisations", name="front_conditions")
     */
    public function conditionUtilisation(): Response
    {
        return $this->render('front/administrative/conditions.html.twig');
    }

    /**
     * @Route("/politique-confidentialite", name="front_pdc")
     */
    public function politiquedeConfidentialite(): Response
    {
        return $this->render('front/administrative/pdc.html.twig');
    }

    /**
     * @Route("/profil/mes_projets/list", name="front_profile_project")
     */
    public function profileProject(): Response
    {
       return $this->render('front/profil/show.html.twig');
    }
}
