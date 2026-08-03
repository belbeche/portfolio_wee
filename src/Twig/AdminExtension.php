<?php

namespace App\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Compteurs de l'administration.
 *
 * Ils alimentent les pastilles de la barre laterale et le tableau de bord.
 * Le calcul est fait une seule fois par requete, puis memorise : la barre
 * laterale est incluse sur chaque page, on ne veut pas rejouer les requetes
 * a chaque affichage.
 *
 * Chaque compteur est protege : si une table n'existe pas encore, par
 * exemple avant la migration des competences, la valeur retombe a zero au
 * lieu de casser toute l'administration.
 */
final class AdminExtension extends AbstractExtension
{
    private EntityManagerInterface $em;
    private ?array $cache = null;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_counts', [$this, 'counts']),
        ];
    }

    public function counts(): array
    {
        if (null !== $this->cache) {
            return $this->cache;
        }

        return $this->cache = [
            'devis' => $this->count('App\Entity\Devis'),
            'callbacks' => $this->count('App\Entity\CallbackRequest'),
            'prospects' => $this->count('App\Entity\Prospect'),
            'projects' => $this->count('App\Entity\Project'),
            'skills' => $this->count('App\Entity\Skill'),
            'tickets' => $this->count('App\Entity\Ticket'),
            'users' => $this->count('App\Entity\User'),
            'subjects' => $this->count('App\Entity\Subject'),
            'comments' => $this->count('App\Entity\Comment'),
            'contacts' => $this->count('App\Entity\Contact'),
            'posts' => $this->count('App\Entity\Post'),
        ];
    }

    private function count(string $class): int
    {
        try {
            return (int) $this->em->createQuery(
                sprintf('SELECT COUNT(e.id) FROM %s e', $class)
            )->getSingleScalarResult();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
