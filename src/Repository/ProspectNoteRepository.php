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

    /**
     * L'historique des e-mails de prospection : ce qui est parti et ce qui a
     * ete refuse, du plus recent au plus ancien. Le prospect est charge dans
     * la meme requete pour ne pas declencher une requete par ligne.
     *
     * @return ProspectNote[]
     */
    public function findEmailHistory(int $limit = 300): array
    {
        return $this->createQueryBuilder('n')
            ->addSelect('p')
            ->leftJoin('n.prospect', 'p')
            ->where('n.type IN (:types)')
            ->setParameter('types', ['email', ProspectNote::TYPE_EMAIL_ECHEC])
            ->orderBy('n.createdAt', 'DESC')
            ->addOrderBy('n.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
