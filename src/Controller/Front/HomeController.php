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
            $phonePrefix = $callbackRequest->getPhonePrefix();
            $phone = $callbackRequest->getPhone();
            $callbackRequest->setPhone($phonePrefix . $phone);

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
                    'user_email' => $callbackRequest->getEmail(),
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Votre demande a été enregistrée avec succès. Nous vous rappellerons bientôt.');

            // Rediriger pour éviter la soumission multiple du formulaire
            return $this->redirectToRoute('front_home');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Une erreur est survenue. Veuillez vérifier les informations saisies.');
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

        // Vérifier si la requête est AJAX (JSON)
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($contact);
                $entityManager->flush();

                // Envoi de l'email
                $email = (new TemplatedEmail())
                    ->from('contact@scriptzenit.fr')
                    ->to($contact->getEmail())
                    ->bcc('wbelbeche.s@gmail.com')
                    ->subject('Prise de contact, ScriptZenIT')
                    ->html($this->renderView('front/contact/email.html.twig', ['contact' => $contact]));

                $mailer->send($email);

                if ($isAjax) {
                    return new JsonResponse(['success' => true, 'message' => 'Votre message a été envoyé avec succès!']);
                }

                // Redirection pour les requêtes non-AJAX
                $this->addFlash('success', 'Votre message a été envoyé avec succès!');
                return $this->redirectToRoute('front_contact');
            } else {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[$error->getOrigin()->getName()] = $error->getMessage();
                }

                if ($isAjax) {
                    return new JsonResponse(['success' => false, 'errors' => $errors], 400);
                }

                // Ajout des erreurs au flash pour les requêtes non-AJAX
                foreach ($errors as $field => $error) {
                    $this->addFlash('error', "$field: $error");
                }
            }
        }

        // Retour HTML pour les requêtes non-AJAX
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
     * @Route("/politique-cookies", name="front_pc")
     */
    public function politiqueCookies(): Response
    {
        return $this->render('front/administrative/pc.html.twig');
    }

    /**
     * @Route("/profil/mes_projets/list", name="front_profile_project")
     */
    public function profileProject(): Response
    {
       return $this->render('front/profil/show.html.twig');
    }

    /**
     * @Route("/expertise_web", name="expertise_web")
     */
    public function expertiseWeb(): Response{
        return $this->render('front/home/web.html.twig');
    }

    /**
     * @Route("/expertise_sur_mesure", name="expertise_sur_mesure")
     */
    public function expertiseSurMesure(): Response{
        return $this->render('front/home/sur_mesure.html.twig');
    }

    /**
     * @Route("/task_automation", name="task_automation")
     */
    public function taskAutomation(): Response{
        return $this->render('front/home/task_automation.html.twig');
    }

    /**
     * @Route("/quote_generation", name="quote_generation")
     */
    public function quoteGeneration(): Response{
        return $this->render('front/home/quote_generation.html.twig');
    }

    /**
     * @Route("/custom_app", name="custom_app")
     */
    public function customApp(): Response{
        return $this->render('front/home/custom_app.html.twig');
    }
}
