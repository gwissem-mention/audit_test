<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

/**
 * Repository de Fiche.
 */
class FicheRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Domaine[] $domains
     *
     * @return int
     */
    public function countPending($domains)
    {
        $qb = $this->createQueryBuilder('f');

        return $qb
            ->select('COUNT(f.id)')
            ->join('f.groupe', 'g')
            ->join(
                'g.domaine',
                'domaine',
                Join::WITH,
                $qb->expr()->in(
                    'domaine',
                    array_map(function (Domaine $domain) {
                        return $domain->getId();
                    }, $domains)
                )
            )
            ->andWhere('f.resolu = FALSE')

            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
