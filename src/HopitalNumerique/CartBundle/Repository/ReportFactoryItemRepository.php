<?php

namespace HopitalNumerique\CartBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\CartBundle\Entity\Item\ReportFactoryItem;
use HopitalNumerique\CartBundle\Entity\Item\ReportItem;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\UserBundle\Entity\User;

class ReportFactoryItemRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return ReportFactoryItem[]
     */
    public function getStagingFactoryItemsForUser(User $user)
    {
        $qb = $this->createQueryBuilder('rfi')
            ->join('rfi.item', 'ri')->addSelect('ri')
            ->join('rfi.reportFactory', 'rf', Join::WITH, 'rf.report IS NULL')->addSelect('rf')

            ->addOrderBy('rfi.position', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ReportItem $reportItem
     * @param User $user
     */
    public function removeByReportItemAndOwner(ReportItem $reportItem, User $user)
    {
        $qb = $this->_em->createQueryBuilder()
            ->delete(ReportFactoryItem::class, 'rfi')
            ->andWhere('rfi.owner = :ownerId')->setParameter('ownerId', $user->getId())
            ->andWhere('rfi.item = :reportItemId')->setParameter('reportItemId', $reportItem->getId())
        ;

        $qb->getQuery()->execute();
    }
}
