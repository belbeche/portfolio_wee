<?php

namespace App\Controller\Front;

use App\Entity\Prospect;
use App\Entity\ProspectNote;
use App\Service\DiagnosticSite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * L'audit gratuit d'un site, ouvert a tout le monde.
 *
 * C'est le meilleur point d'entree du site, pour une raison simple : le
 * visiteur ne demande pas un devis, il demande une information sur SON site.
 * L'engagement est minuscule, la valeur recue est immediate, et celui qui
 * laisse son adresse ensuite est infiniment plus chaud qu'un nom ramasse dans
 * un annuaire.
 *
 * Le moteur est exactement celui de la prospection : un constat affiche ici
 * est celui qui partira dans un e-mail. Il n'y a pas deux verites.
 */
class AuditController extends AbstractController
{
    /** Analyses autorisees par adresse IP et par heure. */
    private const PAR_HEURE = 8;

    /**
     * @Route("/audit", name="front_audit", methods={"GET", "POST"})
     */
    public function index(Request $request, DiagnosticSite $diagnostic, CacheInterface $cache): Response
    {
        $adresse = trim((string) $request->request->get('site'));
        $resultat = null;
        $erreur = null;

        if ($request->isMethod('POST') && '' !== $adresse) {
            if (!$this->isCsrfTokenValid('audit', (string) $request->request->get('_token'))) {
                $erreur = 'Session expirée, recharge la page et réessaie.';
            } elseif (!$this->autorise($request, $cache)) {
                // Ce point d'entree fait sortir une requete HTTP vers un site
                // tiers. Sans plafond, il devient un outil de scan gratuit
                // hebergé chez toi, et ton adresse IP se fait bloquer.
                $erreur = sprintf(
                    "Vous avez lancé %d analyses cette heure-ci. C'est la limite, le temps de laisser respirer le serveur. Réessayez dans un moment.",
                    self::PAR_HEURE
                );
            } else {
                @set_time_limit(30);
                $resultat = $diagnostic->analyserUrl($adresse);

                // On garde le resultat en session : la page du rapport en a
                // besoin, et on ne veut pas relancer l'analyse pour rien.
                $request->getSession()->set('audit_resultat', $resultat);
            }
        }

        // Au retour de la demande de rapport, la page se recharge en GET. Sans
        // ce repechage en session, le score disparait et la personne se
        // retrouve devant un formulaire vide, sans savoir si ca a marche.
        if (null === $resultat && $request->isMethod('GET')) {
            $memoire = $request->getSession()->get('audit_resultat');
            if (is_array($memoire)) {
                $resultat = $memoire;
                $adresse = (string) ($memoire['url'] ?? '');
            }
        }

        return $this->render('front/audit/index.html.twig', [
            'adresse' => $adresse,
            'resultat' => $resultat,
            'erreur' => $erreur,
        ]);
    }

    /**
     * La capture d'adresse : on envoie le rapport et on cree le prospect.
     *
     * @Route("/audit/rapport", name="front_audit_rapport", methods={"POST"})
     */
    public function rapport(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if (!$this->isCsrfTokenValid('audit_rapport', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Session expirée, réessaie.');

            return $this->redirectToRoute('front_audit');
        }

        $resultat = $request->getSession()->get('audit_resultat');
        $email = trim((string) $request->request->get('email'));

        if (!is_array($resultat) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', "Relancez l'analyse puis indiquez une adresse valide.");

            return $this->redirectToRoute('front_audit');
        }

        $this->envoyerRapport($mailer, $email, $resultat);
        $this->enregistrerProspect($em, $email, $resultat, trim((string) $request->request->get('societe')));

        $this->addFlash('success', sprintf(
            'Rapport envoyé à %s. Regardez aussi vos indésirables, un rapport technique y atterrit parfois.',
            $email
        ));

        return $this->redirectToRoute('front_audit');
    }

    // ------------------------------------------------------------- interne

    private function autorise(Request $request, CacheInterface $cache): bool
    {
        $cle = 'audit_'.md5((string) $request->getClientIp());

        $compteur = $cache->get($cle, static function (ItemInterface $item) {
            $item->expiresAfter(3600);

            return 0;
        });

        if ($compteur >= self::PAR_HEURE) {
            return false;
        }

        // On reecrit la valeur en gardant l'expiration d'origine du seau.
        $cache->delete($cle);
        $cache->get($cle, static function (ItemInterface $item) use ($compteur) {
            $item->expiresAfter(3600);

            return $compteur + 1;
        });

        return true;
    }

    /** @param array<string, mixed> $resultat */
    private function envoyerRapport(MailerInterface $mailer, string $email, array $resultat): void
    {
        try {
            $mailer->send(
                (new Email())
                    ->from('contact@walidbelbeche.fr')
                    ->to($email)
                    ->bcc('wbelbeche.s@gmail.com')
                    ->subject(sprintf('Votre rapport : %s', (string) ($resultat['url'] ?? 'votre site')))
                    ->html($this->renderView('front/audit/rapport.html.twig', [
                        'resultat' => $resultat,
                    ]))
            );
        } catch (\Throwable $e) {
            error_log('[audit] envoi du rapport impossible : '.$e->getMessage());
        }
    }

    /**
     * Le visiteur devient un prospect, avec son diagnostic deja rempli.
     *
     * @param array<string, mixed> $resultat
     */
    private function enregistrerProspect(EntityManagerInterface $em, string $email, array $resultat, string $societe): void
    {
        try {
            $repo = $em->getRepository(Prospect::class);
            $prospect = $repo->findOneBy(['email' => $email]);
            $nouveau = null === $prospect;

            if ($nouveau) {
                $prospect = new Prospect();
                $prospect->setEmail($email);
                // Il a demande l'analyse lui-meme : c'est la meilleure piste
                // qui puisse entrer dans ce fichier.
                $prospect->setPriority('P1');
                $prospect->setSource('audit du site');
            }

            if ('' !== $societe && !$prospect->getCompany()) {
                $prospect->setCompany($societe);
            }
            if (!empty($resultat['url'])) {
                $prospect->setWebsite((string) $resultat['url']);
            }
            $prospect->setDiagnostic($resultat);

            if ($nouveau) {
                $em->persist($prospect);
            }

            $note = (new ProspectNote())
                ->setProspect($prospect)
                ->setType('note')
                ->setContent(sprintf(
                    "A demandé l'audit de %s depuis le site public. Score : %s/100.\n%s",
                    (string) ($resultat['url'] ?? '?'),
                    (string) ($resultat['score'] ?? '?'),
                    (string) ($resultat['resume'] ?? '')
                ));
            $em->persist($note);

            $em->flush();
        } catch (\Throwable $e) {
            // Le visiteur a recu son rapport : une erreur d'enregistrement ne
            // doit pas lui afficher un echec.
            error_log('[audit] prospect non enregistre : '.$e->getMessage());
        }
    }
}
