<?php

namespace HopitalNumerique\AutodiagBundle\Repository\Autodiag;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AideBundle\Entity\Aide;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

/**
 * HistoryRepository
 */
class HistoryRepository extends EntityRepository
{

    public function getHistoryByType(Autodiag $autodiag, $type)
    {
        $qb = $this->createQueryBuilder('history');
        $qb
            ->where('history.autodiag = :autodiag')
            ->andWhere('history.type = :type')
            ->orderBy('history.dateTime', 'desc')
            ->setParameters([
                'autodiag' => $autodiag->getId(),
                'type' => $type,
            ]);

        return $qb->getQuery()->getResult();
    }
}
