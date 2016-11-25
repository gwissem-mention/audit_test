<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
            ->join(Autodiag::class, 'autodiag', Join::WITH, 'autodiag.restitution = restitution.id')
            ->leftJoin('restitution.categories', 'categories')
            ->join('categories.items', 'items')
            ->join('items.containers', 'containers')
            ->leftJoin('items.references', 'references')
            ->where('autodiag.id = :id')
            ->setParameter('id', $autodiag->getId())
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
