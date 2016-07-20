<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

/**
 *
 */
class RestitutionRepository extends EntityRepository
{
    public function getForAutodiag(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('restitution');
        $qb
            ->select(
                'restitution',
                'categories',
                'items'
            )
            ->leftJoin('restitution.categories', 'categories')
            ->leftJoin('categories.items', 'items')
            ->where('restitution.id = :id')
            ->setParameter('id', $autodiag->getRestitution()->getId())
        ;

        return $qb->getQuery()->getResult();
    }
}
