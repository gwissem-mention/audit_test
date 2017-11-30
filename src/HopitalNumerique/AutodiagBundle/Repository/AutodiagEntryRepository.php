<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
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
     * Returns users whose last autodiag ($autodiagId) entry update was before $maxUpdateDate.
     *
     * @param integer   $autodiagId
     * @param \DateTime $maxUpdateDate
     *
     * @return QueryBuilder Users
     */
    public function getUpdatersBeforeQueryBuilder($autodiagId, \DateTime $maxUpdateDate)
    {
        return $this->createQueryBuilder('autodiag_entry')
            ->select('user.id')
            ->innerJoin('autodiag_entry.values', 'entry_value')
            ->innerJoin('entry_value.attribute', 'attribute', Join::WITH, 'attribute.autodiag = :autodiag')
            ->innerJoin('autodiag_entry.user', 'user')
            ->groupBy('user.id')
            ->having('MAX(autodiag_entry.updatedAt) < :maxUpdateDate')
            ->setParameters(['autodiag' => (int)$autodiagId, 'maxUpdateDate' => $maxUpdateDate])
        ;
    }
}
