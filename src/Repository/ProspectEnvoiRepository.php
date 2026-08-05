<?php

namespace App\Repository;

use App\Entity\Prospect;
use App\Entity\ProspectEnvoi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProspectEnvoi>
 */
class ProspectEnvoiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProspectEnvoi::class);
    }

    public function parJeton(string $jeton): ?ProspectEnvoi
    {
        return $this->findOneBy(['jeton' => $jeton]);
    }

    /** @return ProspectEnvoi[] */
    public function pourProspect(Prospect $prospect): array
    {
        return $this->findBy(['prospect' => $prospect], ['envoyeLe' => 'ASC']);
    }

    /**
     * Le tableau de bord de la prospection.
     *
     * @return array{envoyes: int, ouverts: int, cliques: int, parEtape: array<int, array{envoyes: int, ouverts: int, cliques: int}>}
     */
    public function statistiques(): array
    {
        $lignes = $this->createQueryBuilder('e')
            ->select('e.etape AS etape, COUNT(e.id) AS envoyes,
                      SUM(CASE WHEN e.ouvertures > 0 THEN 1 ELSE 0 END) AS ouverts,
                      SUM(CASE WHEN e.clics > 0 THEN 1 ELSE 0 END) AS cliques')
            ->groupBy('e.etape')
            ->orderBy('e.etape', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $total = ['envoyes' => 0, 'ouverts' => 0, 'cliques' => 0, 'parEtape' => []];
        foreach ($lignes as $ligne) {
            $etape = (int) $ligne['etape'];
            $total['parEtape'][$etape] = [
                'envoyes' => (int) $ligne['envoyes'],
                'ouverts' => (int) $ligne['ouverts'],
                'cliques' => (int) $ligne['cliques'],
            ];
            $total['envoyes'] += (int) $ligne['envoyes'];
            $total['ouverts'] += (int) $ligne['ouverts'];
            $total['cliques'] += (int) $ligne['cliques'];
        }

        return $total;
    }

    /**
     * Les prospects qui ont ouvert au moins une fois et n'ont jamais repondu :
     * ce sont eux qu'il faut appeler, pas les autres.
     *
     * @return ProspectEnvoi[]
     */
    public function interesses(int $limit = 30): array
    {
        return $this->createQueryBuilder('e')
            ->addSelect('p')
            ->join('e.prospect', 'p')
            ->where('e.ouvertures > 0')
            ->andWhere('p.status IN (:actifs)')
            ->setParameter('actifs', [Prospect::STATUS_CONTACTED, Prospect::STATUS_FOLLOWED_UP])
            ->orderBy('e.clics', 'DESC')
            ->addOrderBy('e.ouvertures', 'DESC')
            ->addOrderBy('e.ouvertLe', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
