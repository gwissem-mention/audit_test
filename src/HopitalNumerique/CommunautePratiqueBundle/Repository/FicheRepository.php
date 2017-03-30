<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

/**
 * Repository de Fiche.
 */
class FicheRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return int
     */
    public function countPending()
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.resolu = FALSE')

            ->getQuery()->getSingleScalarResult()
        ;
    }
}
