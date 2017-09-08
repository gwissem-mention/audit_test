<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

/**
 * HistoryRepository.
 */
class AutodiagRepository extends EntityRepository
{
    /**
     * @param Domaine $domain
     *
     * @return Autodiag\|null
     */
    public function getRandomAutodiagForDomain(Domaine $domain)
    {
        return $this->createQueryBuilder('a ')
            ->addSelect('RAND() as HIDDEN random')
            ->join('a .domaines', 'd', Join::WITH, 'd.id = :domainId')
            ->setParameter('domainId', $domain->getId())

            ->addOrderBy('random')

            ->setMaxResults(1)

            ->getQuery()->getOneOrNullResult()
        ;
    }

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
                'autodiagId' => $autodiagId,
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
                'COUNT(DISTINCT entries_valid_syntheses.id) as nb_entries_valid',
                'COUNT(DISTINCT entries_in_progress_syntheses.id) as nb_entries_in_progress'
            )
            ->join('ad.domaines', 'domaines', Join::WITH, 'domaines.id IN (:domaine_ids)')
            ->leftJoin(Synthesis::class,'synthesis_valid', Join::WITH,'synthesis_valid.autodiag = ad.id AND synthesis_valid.validatedAt IS NOT NULL')
            ->leftJoin('synthesis_valid.entries', 'entries_valid')

            ->leftJoin('entries_valid.syntheses', 'entries_valid_syntheses', Join::WITH, 'entries_valid_syntheses.autodiag=ad.id AND entries_valid_syntheses.validatedAt IS NOT NULL AND entries_valid_syntheses.createdFrom IS NULL')

            ->leftJoin(
                Synthesis::class,
                'synthesis_in_progress',
                Join::WITH,
                'synthesis_in_progress.autodiag = ad.id AND synthesis_in_progress.validatedAt IS NULL'
            )
                ->leftJoin('synthesis_in_progress.entries', 'entries_in_progress')
            ->leftJoin('entries_in_progress.syntheses', 'entries_in_progress_syntheses', Join::WITH, 'entries_in_progress_syntheses.autodiag=ad.id AND entries_in_progress_syntheses.validatedAt IS NULL AND entries_in_progress_syntheses.createdFrom IS NULL')
            ->groupBy('ad.id')
            ->setParameters([
                'domaine_ids' => $domainesIds,
            ])
        ;

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
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

    public function getComputeBeginning($autodiagId)
    {
        $qb = $this->createQueryBuilder('autodiag');
        $qb
            ->select('autodiag.computeBeginning')
            ->where('autodiag.id = :autodiag_id')
            ->setParameter('autodiag_id', $autodiagId);

        return $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }
}
