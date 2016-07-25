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
                'chapters',
                'attributes',
                'options'
            )
            ->join('ad.containers', 'chapters', Join::WITH, $qb->expr()->isInstanceOf('chapters', Chapter::class))
            ->join('ad.attributes', 'attributes')
            ->leftJoin('attributes.options', 'options')
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
                'COUNT(entries_valid.id) as nb_entries_valid',
                'COUNT(entries_in_progress.id) as nb_entries_in_progress'
            )
            ->join('ad.domaines', 'domaines', Join::WITH, 'domaines.id IN (:domaine_ids)')
            ->leftJoin(Synthesis::class, 'synthesis', Join::WITH, 'synthesis.autodiag = ad.id')
            ->leftJoin('synthesis.entries', 'entries_valid', Join::WITH, 'entries_valid.valid = true')
            ->leftJoin('synthesis.entries', 'entries_in_progress', Join::WITH, 'entries_in_progress.valid = false')
            ->groupBy('ad.id')
            ->setParameters([
                'domaine_ids' => $domainesIds
            ])
        ;

        return $qb->getQuery()->getArrayResult();
    }
}
