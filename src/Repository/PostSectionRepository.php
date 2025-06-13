<?php

namespace App\Repository;

use App\Entity\PostSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostSection>
 *
 * @method PostSection|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostSection|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostSection[]    findAll()
 * @method PostSection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostSection::class);
    }

    public function add(PostSection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PostSection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
