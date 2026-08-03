<?php

namespace App\Repository;

use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Skill>
 */
class SkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    /** @return Skill[] */
    public function findVisible(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.visible = true')
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.level', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Skill[] */
    public function findHighlighted(int $limit = 8): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.visible = true')
            ->andWhere('s.highlighted = true')
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.level', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Competences visibles, regroupees par famille, dans l'ordre declare
     * par Skill::GROUPS.
     *
     * @return array<string, Skill[]>
     */
    public function findGroupedByFamily(): array
    {
        $grouped = [];

        foreach ($this->findVisible() as $skill) {
            $grouped[$skill->getSkillGroup()][] = $skill;
        }

        $ordered = [];
        foreach (array_keys(Skill::GROUPS) as $key) {
            if (!empty($grouped[$key])) {
                $ordered[$key] = $grouped[$key];
            }
        }

        return $ordered + $grouped;
    }
}
