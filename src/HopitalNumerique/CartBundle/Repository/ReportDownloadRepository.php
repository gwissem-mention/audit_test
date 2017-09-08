<?php

namespace HopitalNumerique\CartBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class ReportDownloadRepository
 */
class ReportDownloadRepository extends EntityRepository
{
    /**
     * @param Report $report
     * @param User   $user
     *
     * @return mixed
     */
    public function findOneByReportAndUser(Report $report, User $user)
    {
        return $this->createQueryBuilder('report_download')
            ->join('report_download.report', 'report', Join::WITH, 'report.id = :reportId')
            ->setParameter('reportId', $report->getId())
            ->join('report_download.user', 'user', Join::WITH, 'user.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
