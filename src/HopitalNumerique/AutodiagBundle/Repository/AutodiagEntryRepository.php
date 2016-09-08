<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

class AutodiagEntryRepository extends EntityRepository
{
    /**
     * Get original entries (not copies) by autodiag
     *
     * @param Autodiag $autodiag
     * @return array
     */
    public function findOriginalByAutodiag(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('entry');
        $qb
            ->addSelect('values')
            ->join('entry.syntheses', 'syntheses')
            ->leftJoin('entry.values', 'values')
            ->where('syntheses.autodiag = :autodiag_id')
            ->andWhere('entry.copy = FALSE')
            ->setParameters([
                'autodiag_id' => $autodiag->getId()
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}
