<?php

namespace App\Repository;

use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Facture>
 */
class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    /**
     * Le prochain numero de la serie annuelle : FAC-2026-0001, 0002, etc.
     *
     * La numerotation doit etre continue et sans trou. On lit donc le dernier
     * numero reellement attribue plutot que de compter les factures, sinon une
     * suppression ferait reutiliser un numero deja emis.
     */
    public function prochainNumero(?int $annee = null): string
    {
        $annee = $annee ?: (int) date('Y');
        $prefixe = 'FAC-'.$annee.'-';

        $dernier = $this->createQueryBuilder('f')
            ->select('f.numero')
            ->where('f.numero LIKE :prefixe')
            ->setParameter('prefixe', $prefixe.'%')
            ->orderBy('f.numero', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $suivant = 1;
        if (null !== $dernier) {
            $suivant = 1 + (int) substr((string) $dernier['numero'], strlen($prefixe));
        }

        return $prefixe.str_pad((string) $suivant, 4, '0', STR_PAD_LEFT);
    }

    /** @return Facture[] */
    public function recentes(int $limit = 200): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.emiseLe', 'DESC')
            ->addOrderBy('f.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
