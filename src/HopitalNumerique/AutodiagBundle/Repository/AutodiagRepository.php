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

    public function getFullyLoaded(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('ad');
        $qb
            ->select(
                'ad',
                'chapters'
            )
            ->join('ad.containers', 'chapters', Join::WITH, $qb->expr()->isInstanceOf('chapters', Chapter::class))
//            ->join('ad.attributes', 'attributes')
            ->where(
                $qb->expr()->eq('ad', ':autodiag')
            )
            ->setParameters([
                'autodiag' => $autodiag
            ])
        ;
//
//        $autodiag = $qb->getQuery()->getOneOrNullResult();
//        foreach ($autodiag->getChapters() as $chapter) {
//            dump($chapter->getAttributes());die;
//        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}
