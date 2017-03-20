<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Restitution\Item;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

class RestitutionRepository extends EntityRepository
{
    /**
     * Get Autodiag restitution.
     *
     * @param Autodiag  $autodiag
     * @param Synthesis $synthesis
     *
     * @return mixed
     */
    public function getForAutodiag(Autodiag $autodiag, Synthesis $synthesis = null)
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
        
        if (!is_null($synthesis) && $synthesis->getEntries()->count() > 1) {
            $qb
                ->andWhere('items.type != :responseType')
                ->setParameter('responseType', Item::RESPONSE_TYPE)
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}
