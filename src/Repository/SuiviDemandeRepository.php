<?php

namespace App\Entity;

use App\Entity\SuiviDemande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuiviDemande>
 *
 * @method SuiviDemande|null find($id, $lockMode = null, $lockVersion = null)
 * @method SuiviDemande|null findOneBy(array $criteria, array $orderBy = null)
 * @method SuiviDemande[]    findAll()
 * @method SuiviDemande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuiviDemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuiviDemande::class);
    }

//    /**
//     * @return SuiviDemande[] Returns an array of SuiviDemande objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SuiviDemande
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
