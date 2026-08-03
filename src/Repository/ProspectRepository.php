<?php

namespace App\Repository;

use App\Entity\Prospect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prospect>
 *
 * @method Prospect|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prospect|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prospect[]    findAll()
 * @method Prospect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProspectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prospect::class);
    }

    /**
     * Les prospects dont la relance est due : le cœur du système.
     *
     * @return Prospect[]
     */
    public function findDueForFollowUp(?\DateTimeInterface $now = null): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nextFollowUpAt IS NOT NULL')
            ->andWhere('p.nextFollowUpAt <= :now')
            ->andWhere('p.status NOT IN (:closed)')
            ->setParameter('now', $now ?: new \DateTime())
            ->setParameter('closed', Prospect::CLOSED_STATUSES)
            ->orderBy('p.nextFollowUpAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * La file d'attente du jour : les cibles jamais contactées, les mieux
     * qualifiées d'abord.
     *
     * @return Prospect[]
     */
    public function findNextToContact(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Prospect::STATUS_TO_CONTACT)
            // P1 (aucune offre mobile) avant P2, P2 avant P3.
            ->orderBy('p.priority', 'ASC')
            ->addOrderBy('p.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Prospects contactés mais restés sans relance programmée : le trou noir
     * classique d'un pipeline mal tenu.
     *
     * @return Prospect[]
     */
    public function findOrphans(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nextFollowUpAt IS NULL')
            ->andWhere('p.lastContactedAt IS NOT NULL')
            ->andWhere('p.status NOT IN (:closed)')
            ->setParameter('closed', Prospect::CLOSED_STATUSES)
            ->orderBy('p.lastContactedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compteurs du tunnel, pour le tableau de bord d'administration.
     *
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('p.status AS status, COUNT(p.id) AS total')
            ->groupBy('p.status')
            ->getQuery()
            ->getArrayResult();

        $counts = array_fill_keys(array_keys(Prospect::STATUSES), 0);

        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Taux de réponse : part des prospects contactés qui ont dépassé le stade
     * de la simple relance. En dessous de 10 %, le problème est le message ou
     * le ciblage, pas le volume.
     */
    public function computeResponseRate(): float
    {
        $counts = $this->countByStatus();

        $contacted = $counts[Prospect::STATUS_CONTACTED]
            + $counts[Prospect::STATUS_FOLLOWED_UP]
            + $counts[Prospect::STATUS_MEETING]
            + $counts[Prospect::STATUS_PROPOSAL]
            + $counts[Prospect::STATUS_WON]
            + $counts[Prospect::STATUS_LOST];

        if (0 === $contacted) {
            return 0.0;
        }

        $responded = $counts[Prospect::STATUS_MEETING]
            + $counts[Prospect::STATUS_PROPOSAL]
            + $counts[Prospect::STATUS_WON];

        return round(($responded / $contacted) * 100, 1);
    }
}
