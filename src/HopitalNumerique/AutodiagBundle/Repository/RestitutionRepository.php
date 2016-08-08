<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

/**
 *
 */
class RestitutionRepository extends EntityRepository
{
    /**
     * Get Autodiag restitution
     *
     * @param Autodiag $autodiag
     * @return mixed
     */
    public function getForAutodiag(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('restitution');
        $qb
            ->select(
                'restitution',
                'categories',
                'items',
                'containers',
                'references'
            )
            ->leftJoin('restitution.categories', 'categories')
            ->leftJoin('categories.items', 'items')
            ->leftJoin('items.containers', 'containers')
            ->leftJoin('items.references', 'references')
            ->where('restitution.id = :id')
            ->setParameter('id', $autodiag->getRestitution()->getId())
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
