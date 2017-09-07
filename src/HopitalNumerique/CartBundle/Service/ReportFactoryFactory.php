<?php

namespace HopitalNumerique\CartBundle\Service;

use HopitalNumerique\CartBundle\Entity\Item\ReportFactoryItem;
use HopitalNumerique\CartBundle\Entity\ReportFactory;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\Report;

class ReportFactoryFactory
{

    /**
     * @param User $user
     * @param Report|null $report
     *
     * @return ReportFactory
     */
    public function buildReportFactory(User $user, Report $report = null)
    {
        $reportFactory = new ReportFactory($user, $report);

        foreach ($report->getItems() as $reportItem) {
            $reportFactory->addFactoryItem(new ReportFactoryItem($reportFactory, $user, $reportItem, $reportItem->getPosition()));
        }

        return $reportFactory;
    }
}
