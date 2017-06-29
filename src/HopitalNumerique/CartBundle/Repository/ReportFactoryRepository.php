<?php

namespace HopitalNumerique\CartBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\CartBundle\Entity\Item\ReportFactoryItem;
use HopitalNumerique\CartBundle\Entity\Item\ReportItem;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\UserBundle\Entity\User;

class ReportFactoryRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return ReportFactoryItem[]
     */
    public function getStagingFactoryForUser(User $user)
    {
        $qb = $this->createQueryBuilder('rf')
            ->leftJoin('rf.factoryItems', 'rfi')->addSelect('rfi')
            ->leftJoin('rfi.item', 'ri')->addSelect('ri')
            ->andWhere('rf.report IS NULL')
            ->andWhere('rf.owner = :ownerId')->setParameter('ownerId', $user->getId())
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
