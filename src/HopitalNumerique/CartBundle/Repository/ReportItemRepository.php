<?php

namespace HopitalNumerique\CartBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\CartBundle\Entity\Item\ReportItem;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\UserBundle\Entity\User;

class ReportItemRepository extends EntityRepository
{
    /**
     * @param Report $report
     *
     * @return ReportItem[]|array
     */
    public function getDisplayableItemsForReport(Report $report)
    {
        $qb = $this->createQueryBuilder('ri')
            ->join('ri.report', ' r', Join::WITH, 'r.id = :reportId')
            ->setParameter('reportId', $report->getId())

            ->addOrderBy('ri.position', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }
}
