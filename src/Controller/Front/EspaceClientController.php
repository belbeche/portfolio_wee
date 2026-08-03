<?php

namespace App\Controller\Front;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Espace client.
 *
 * Toutes ces fonctionnalités existaient déjà (devis, tickets d'assistance,
 * sujets, favoris, profil) mais restaient dispersées dans des menus séparés,
 * et l'entrée elle-même était commentée dans la navigation : personne ne
 * pouvait la trouver. Ce tableau de bord les rassemble en une page unique.
 */
class EspaceClientController extends AbstractController
{
    /**
     * @Route("/espace", name="front_espace_client")
     */
    public function dashboard(): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // On passe par les collections de l'entité User : les relations sont
        // déjà déclarées, inutile d'interroger les dépôts et de dupliquer
        // la connaissance des noms de propriétés.
        $devis = $user->getDevis();
        $tickets = $user->getSentMessages();

        return $this->render('front/espace/dashboard.html.twig', [
            'devisList' => $this->latest($devis, 5),
            'ticketList' => $this->latest($tickets, 5),
            'countDevis' => count($devis),
            'countTickets' => count($tickets),
            'countSubjects' => count($user->getSubject()),
            'countLikes' => count($user->getLikes()),
        ]);
    }

    /**
     * Retourne les N derniers éléments d'une collection Doctrine,
     * du plus récent au plus ancien.
     */
    private function latest(iterable $collection, int $max): array
    {
        $items = is_array($collection) ? $collection : iterator_to_array($collection);

        return array_slice(array_reverse($items), 0, $max);
    }
}
