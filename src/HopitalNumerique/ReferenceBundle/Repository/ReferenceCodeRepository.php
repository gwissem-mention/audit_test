<?php

namespace HopitalNumerique\ReferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ReferenceCodeRepository
 */
class ReferenceCodeRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getDistinctCodes()
    {
        return $this->createQueryBuilder('referenceCode')
            ->select('DISTINCT(referenceCode.label)')
            ->getQuery()
            ->getResult()
        ;
    }
}
