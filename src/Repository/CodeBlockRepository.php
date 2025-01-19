<?php

namespace App\Repository;

use App\Entity\CodeBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CodeBlock>
 *
 * @method CodeBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeBlock[]    findAll()
 * @method CodeBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeBlock::class);
    }

//    /**
//     * @return CodeBlock[] Returns an array of CodeBlock objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CodeBlock
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
