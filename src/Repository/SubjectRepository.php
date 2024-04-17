<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Subject;
use App\Form\Model\SearchModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Subject>
 *
 * @method Subject|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subject|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subject[]    findAll()
 * @method Subject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subject::class);
    }

    public function add(Subject $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Subject $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param SearchModel $searchModel
     * @return array
     */
    public function findBySearch(SearchModel $searchModel)
    {
        $queryBuilder = $this
            ->createQueryBuilder('a')
            ->where('a.isPublished = :isPublished')
            ->setParameter(':isPublished', true);

        if ($searchModel->getTerm()) {
            $queryBuilder
                ->andwhere('a.content LIKE :term or a.title LIKE :term')
                ->setParameter(':term', '%' . $searchModel->getTerm() . '%');
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function findByCategoryName($categoryName)
    {
        return $this
            ->createQueryBuilder('a')
            ->innerJoin('a.categories', 'ca')
            ->where('ca.name = :categoryName')
            ->andWhere('a.active = :active')
            ->orderBy('a.createdAt', 'DESC')
            ->setParameter(':categoryName', $categoryName)
            ->setParameter(':active', true)
            ->getQuery()
            ->getResult();
    }

    public function findBySubject(){
        return $this
            ->createQueryBuilder('a') // Utilisation de l'alias 'a'
            ->where('a.isPublished = :published OR a.isPublished = :unpublished') // Utilisation de l'alias 'a'
            ->orderBy('a.id', 'DESC') // Utilisation de l'alias 'a'
            ->setParameter('published', true)
            ->setParameter('unpublished', false)
            ->getQuery();
    }






//    /**
//     * @return Subject[] Returns an array of Subject objects
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

//    public function findOneBySomeField($value): ?Subject
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
