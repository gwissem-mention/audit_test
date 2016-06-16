<?php

namespace HopitalNumerique\AutodiagBundle\Repository\Autodiag;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AideBundle\Entity\Aide;

/**
 * HistoryRepository
 */
class HistoryRepository extends EntityRepository
{

    public function getHistoryByType($type)
    {
        $qb = $this->createQueryBuilder('history');
        $qb
            ->where('history.type = :type')
            ->orderBy('history.dateTime', 'desc')
            ->setParameter('type', $type);

        return $qb->getQuery()->getResult();
    }
}
