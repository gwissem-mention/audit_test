<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\UserBundle\Entity\User;

class AutodiagEntryRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return integer
     */
    public function countActiveForUser(User $user)
    {
        return $this->_em->createQueryBuilder()
            ->select('COUNT(e)')
            ->from(AutodiagEntry::class, 'e')
            ->andWhere('e.user = :userId')->setParameter('userId', $user->getId())

            ->getQuery()->getSingleScalarResult()
        ;
    }
}
