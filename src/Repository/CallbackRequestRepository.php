<?php

namespace App\Repository;

use App\Entity\CallbackRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CallbackRequest>
 *
 * @method CallbackRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method CallbackRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method CallbackRequest[]    findAll()
 * @method CallbackRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallBackRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CallbackRequest::class);
    }
   
//    /**
//     * @return CallbackRequest[] Returns an array of CallbackRequest objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CallbackRequest
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
