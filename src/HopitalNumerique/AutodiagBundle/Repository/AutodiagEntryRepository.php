<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

class AutodiagEntryRepository extends EntityRepository
{
    public function findByAutodiag(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('entry');
        $qb
            ->addSelect('values')
            ->join('entry.syntheses', 'syntheses')
            ->leftJoin('entry.values', 'values')
            ->where('syntheses.autodiag = :autodiag_id')
            ->setParameters([
                'autodiag_id' => $autodiag->getId()
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}
