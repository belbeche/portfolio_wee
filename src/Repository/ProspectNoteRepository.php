<?php

namespace App\Repository;

use App\Entity\ProspectNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProspectNote>
 */
class ProspectNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProspectNote::class);
    }

    /**
     * Les derniers echanges, tous prospects confondus.
     *
     * @return ProspectNote[]
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
