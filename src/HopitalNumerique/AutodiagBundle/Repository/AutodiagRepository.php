<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;

/**
 * HistoryRepository
 */
class AutodiagRepository extends EntityRepository
{

    public function getFullyLoaded($autodiagId)
    {
        $qb = $this->createQueryBuilder('ad');
        $qb
            ->select(
                'ad',
                'chapters',
                'attributes',
                'options'
            )
            ->join('ad.containers', 'chapters', Join::WITH, $qb->expr()->isInstanceOf('chapters', Chapter::class))
            ->join('ad.attributes', 'attributes')
            ->join('attributes.options', 'options')
            ->where(
                $qb->expr()->eq('ad.id', ':autodiagId')
            )
            ->setParameters([
                'autodiagId' => $autodiagId
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getDatasForGrid()
    {
        $qb = $this->createQueryBuilder('ad');
        $qb
            ->select(
                'ad.id',
                'ad.title',
                'GROUP_CONCAT(domaines.nom) AS domaines_list',
                'ad.createdAt',
                'ad.publicUpdatedDate'
            )
            ->join('ad.domaines', 'domaines')
        ;

        return $qb->getQuery()->getArrayResult();
    }
}
