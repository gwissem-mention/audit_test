<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Repository de Fiche.
 */
class FicheRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Domaine $domain
     *
     * @return int
     */
    public function countPending(Domaine $domain)
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->join('f.groupe', 'g')
            ->join('g.domaine', 'd', Join::WITH, 'd.id = :domaine')
            ->setParameter('domaine', $domain->getId())
            ->andWhere('f.resolu = FALSE')

            ->getQuery()->getSingleScalarResult()
        ;
    }
}
