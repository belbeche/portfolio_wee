<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Contact;
use App\Entity\Member;
use App\Entity\Project;
use App\Repository\SkillRepository;

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
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class HomeController extends AbstractController
{

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    /**
    * @Route("/", name="front_home")
    * @Route("/realisations/{category}", name="front_project_by_category")
    */
    public function index(EntityManagerInterface $entityManager, Request $request, MailerInterface $mailer, SkillRepository $skillRepository, ?string $category = null): Response
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
                ->from(new Address('contact@walidbelbeche.fr', 'Walid BELBECHE - Demande de rappel'))
                ->to($form->get('email')->getData())
                ->bcc('wbelbeche.s@gmail.com')
                ->subject('Nouvelle demande de rappel')
                ->htmlTemplate('front/home/callback_request.html.twig')
                ->context([
                    'name' => $callbackRequest->getName(),
                    'phone' => $callbackRequest->getPhone(),
                    'user_email' => $callbackRequest->getEmail(),
                    'locale' => $request->getLocale(),
                    'contact' => $entityManager->getRepository(Contact::class)->findOneBy(['email' => $callbackRequest->getEmail()]),
                ]);

            // La demande est deja enregistree en base : un serveur mail en
            // panne ne doit pas faire echouer le parcours du prospect.
            try {
                $mailer->send($email);
            } catch (\Throwable $e) {
                // Visible dans var/log, invisible pour le visiteur : sa
                // demande est bien arrivee, seul l'accuse par e-mail manque.
                error_log('[callback] envoi e-mail impossible : '.$e->getMessage());
            }

            $this->addFlash('success', $this->translator->trans('flash.success.callback_request'));

            // Rediriger pour éviter la soumission multiple du formulaire
            return $this->redirectToRoute('front_home');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', $this->translator->trans('flash.error.input_invalid'));
        }

        if ($category) {
            $projects = $entityManager->getRepository(Project::class)->findBy(['category' => $category]);
        } else {
            $projects = $entityManager->getRepository(Project::class)
                ->findBy([], ['featured' => 'DESC', 'position' => 'ASC', 'id' => 'DESC']);
        }

        return $this->render('front/home/index.html.twig', [
            'projects' => $projects,
            'form' => $form->createView(),
            // Competences administrables, regroupees par famille.
            'skills' => $skillRepository->findGroupedByFamily(),
            // Avis clients moderes, affiches sur l'accueil.
            'reviews' => $entityManager->getRepository(\App\Entity\Review::class)->findApproved(3),
            // L'equipe, administrable depuis le back office. Le dirigeant
            // d'abord, le reste ensuite : la promesse du site est qu'on parle
            // a une seule personne, l'equipe vient en soutien.
            'members' => $entityManager->getRepository(Member::class)
                ->findBy([], ['id' => 'ASC']),
        ]);
    }


    /**
     * Resume public de la supervision, pour le bandeau temps reel de l'accueil.
     *
     * Volontairement anonyme : des compteurs, rien d'autre. Aucun nom de
     * serveur ni identifiant ne sort d'ici, un visiteur ne peut donc rien
     * deduire des clients heberges.
     *
     * Le resultat est mis en cache deux minutes. Sans cela, chaque visite de
     * l'accueil declencherait une rafale d'appels au panneau, et un robot
     * suffirait a le faire tomber.
     *
     * @Route("/api/supervision", name="api_supervision_public", methods={"GET"})
     */
    public function supervisionPublique(\App\Service\PterodactylService $pterodactyl, CacheInterface $cache): JsonResponse
    {
        $resume = $cache->get('supervision_publique', function (ItemInterface $item) use ($pterodactyl) {
            $item->expiresAfter(120);

            return $pterodactyl->publicSummary(30);
        });

        $reponse = new JsonResponse($resume);
        // Deux minutes de cache navigateur et CDN : meme logique que le cache
        // serveur, pour que Cloudflare absorbe l'essentiel du trafic.
        $reponse->setPublic();
        $reponse->setMaxAge(120);
        $reponse->headers->addCacheControlDirective('s-maxage', 120);

        return $reponse;
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
                    ->from('contact@walidbelbeche.fr')
                    ->to($contact->getEmail())
                    ->bcc('wbelbeche.s@gmail.com')
                    ->subject('Prise de contact, Walid BELBECHE')
                    ->html($this->renderView('front/contact/email.html.twig', [
                        'contact' => $contact,
                        'locale' => $request->getLocale(),
                    ]));

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
     * Les trois formules : Diagnostic, Projet, Interlocuteur Unique.
     *
     * @Route("/mes-formules", name="front_services")
     */
    public function services(): Response
    {
        return $this->render('front/home/services.html.twig');
    }
    /**
     * Anciennes pages de service, fusionnees dans /mes-formules.
     * Redirection 301 : les liens externes et le referencement suivent.
     *
     * @Route("/expertise_web", name="expertise_web")
     * @Route("/expertise_sur_mesure", name="expertise_sur_mesure")
     * @Route("/task_automation", name="task_automation")
     * @Route("/quote_generation", name="quote_generation")
     * @Route("/custom_app", name="custom_app")
     */
    public function legacyServices(): Response
    {
        return $this->redirectToRoute('front_services', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
