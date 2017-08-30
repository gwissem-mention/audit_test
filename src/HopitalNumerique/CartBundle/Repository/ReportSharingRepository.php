<?php

namespace HopitalNumerique\CartBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CartBundle\Entity\ReportSharing;
use HopitalNumerique\UserBundle\Entity\User;

class ReportSharingRepository extends EntityRepository
{
    /**
     * Returns user who are origin or target of report shares ($reportId).
     *
     * @param integer $reportId
     *
     * @return QueryBuilder
     */
    public function getSharingUsersFromReportQueryBuilder($reportId)
    {
        return $this->createQueryBuilder('report_sharing')
            ->select('user.id')
            ->join(
                User::class,
                'user',
                Join::WITH,
                '(report_sharing.target = user.id OR report_sharing.sharedBy = user.id)
                AND report_sharing.report = :reportId
                AND report_sharing.type = :typeShare'
            )
            ->setParameters([
                'reportId' => (int)$reportId,
                'typeShare' => ReportSharing::TYPE_SHARE,
            ]);
    }
}
