<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

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
                'chapters'
            )
            ->join('ad.containers', 'chapters', Join::WITH, $qb->expr()->isInstanceOf('chapters', Chapter::class))
            ->where(
                $qb->expr()->eq('ad.id', ':autodiagId')
            )
            ->setParameters([
                'autodiagId' => $autodiagId
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getDatasForGrid($domaines)
    {
        /** @var Collection $domaines */
        $domaines = $domaines->value;
        $domainesIds = $domaines->map(function ($domaine) {
            return $domaine->getId();
        })->toArray();

        $qb = $this->createQueryBuilder('ad');
        $qb
            ->select(
                'ad.id',
                'ad.title',
                'GROUP_CONCAT(DISTINCT domaines.nom SEPARATOR \', \') AS domaines_list',
                'ad.createdAt',
                'ad.publicUpdatedDate',
                'COUNT(DISTINCT entries_valid.id) as nb_entries_valid',
                'COUNT(DISTINCT entries_in_progress.id) as nb_entries_in_progress'
            )
            ->join('ad.domaines', 'domaines', Join::WITH, 'domaines.id IN (:domaine_ids)')
            ->leftJoin(
                Synthesis::class,
                'synthesis_valid',
                Join::WITH,
                'synthesis_valid.autodiag = ad.id AND synthesis_valid.validatedAt IS NOT NULL'
            )
                ->leftJoin('synthesis_valid.entries', 'entries_valid')
            ->leftJoin(
                Synthesis::class,
                'synthesis_in_progress',
                Join::WITH,
                'synthesis_in_progress.autodiag = ad.id AND synthesis_in_progress.validatedAt IS NULL'
            )
                ->leftJoin('synthesis_in_progress.entries', 'entries_in_progress')
            ->groupBy('ad.id')
            ->setParameters([
                'domaine_ids' => $domainesIds
            ])
        ;

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @return Autodiag[]
     */
    public function getAllBetweenDate(\DateTime $start = null, \DateTime $end = null)
    {
        $qb = $this->createQueryBuilder('autodiag');

        if (!is_null($start)) {
            $qb->where('autodiag.createdAt <= :start')
                ->setParameter('start', $start);
        }

        if (!is_null($end)) {
            $qb->andWhere('autodiag.createdAt >= :end')
                ->setParameter('end', $end);
        }

        return $qb->getQuery()->getResult();
    }
}
