<?php

namespace App\Controller\Front;

use App\Repository\ProspectEnvoiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Le suivi des e-mails de prospection : ouvertures et clics.
 *
 * Deux precautions de conception :
 *
 *   - l'adresse ne contient qu'un jeton aleatoire, jamais l'identifiant du
 *     prospect ni son adresse e-mail. Un tiers qui intercepte le lien
 *     n'apprend rien sur qui est suivi.
 *   - la destination des clics n'est PAS passee en clair dans l'URL mais
 *     choisie dans une liste fermee. Sans cela, ce point d'entree devient une
 *     redirection ouverte, exactement ce que les campagnes de hameconnage
 *     cherchent pour emprunter la reputation d'un domaine honnete.
 */
class SuiviController extends AbstractController
{
    private const DESTINATIONS = [
        'realisations' => 'https://walidbelbeche.fr/realisations',
        'devis' => 'https://walidbelbeche.fr/devis',
        'methode' => 'https://walidbelbeche.fr/ma-methode',
        'site' => 'https://walidbelbeche.fr',
    ];

    /**
     * Le pixel de suivi. Repond toujours une image, meme si le jeton est
     * inconnu : une reponse differente permettrait de deviner les jetons
     * valides.
     *
     * @Route("/p/{jeton}.gif", name="suivi_ouverture", methods={"GET"}, requirements={"jeton"="[a-f0-9]{24}"})
     */
    public function ouverture(string $jeton, ProspectEnvoiRepository $envois, EntityManagerInterface $em): Response
    {
        $envoi = $envois->parJeton($jeton);
        if (null !== $envoi) {
            $envoi->marquerOuvert();
            $em->flush();
        }

        // GIF transparent de 1 pixel, le plus petit fichier image valide.
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        $reponse = new Response($gif, 200, ['Content-Type' => 'image/gif']);
        $reponse->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $reponse->headers->set('Pragma', 'no-cache');

        return $reponse;
    }

    /**
     * @Route("/c/{jeton}/{vers}", name="suivi_clic", methods={"GET"}, requirements={"jeton"="[a-f0-9]{24}", "vers"="[a-z]+"})
     */
    public function clic(string $jeton, string $vers, ProspectEnvoiRepository $envois, EntityManagerInterface $em): RedirectResponse
    {
        $envoi = $envois->parJeton($jeton);
        if (null !== $envoi) {
            $envoi->marquerClique();
            $em->flush();
        }

        return new RedirectResponse(self::DESTINATIONS[$vers] ?? self::DESTINATIONS['site'], 302);
    }
}
