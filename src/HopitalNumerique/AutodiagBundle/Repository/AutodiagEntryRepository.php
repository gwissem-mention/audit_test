<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
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

    /**
     * Returns last entry modification date for given user and autodiag.
     *
     * @param User     $user
     * @param Autodiag $autodiag
     *
     * @return string|null
     */
    public function getLastUserEntryUpdate(User $user, Autodiag $autodiag)
    {
        return $this->createQueryBuilder('autodiag_entry')
            ->select('MAX(autodiag_entry.updatedAt)')
            ->innerJoin('autodiag_entry.values', 'entry_value')
            ->innerJoin('entry_value.attribute', 'attribute', Join::WITH, 'attribute.autodiag = :autodiag')
            ->where('autodiag_entry.user = :user')
            ->setParameters(['user' => $user, 'autodiag' => $autodiag])
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR)
        ;
    }

}
