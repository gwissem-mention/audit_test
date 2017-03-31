<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Repository de Fiche.
 */
class FicheRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param User $user
     *
     * @return int
     */
    public function countPending(User $user)
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->join('f.groupe', 'g')
            ->join('g.domaine', 'd', Join::WITH, 'd.id IN (:domains)')
            ->setParameter('domains', $user->getDomaines())
            ->andWhere('f.resolu = FALSE')

            ->getQuery()->getSingleScalarResult()
        ;
    }
}
