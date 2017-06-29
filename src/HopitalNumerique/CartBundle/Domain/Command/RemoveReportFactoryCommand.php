<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\CartBundle\Entity\ReportFactory;

class RemoveReportFactoryCommand
{
    /**
     * @var ReportFactory $reportFactory
     */
    public $reportFactory;

    /**
     * removeReportFactoryCommand constructor.
     *
     * @param ReportFactory $reportFactory
     */
    public function __construct(ReportFactory $reportFactory)
    {
        $this->reportFactory = $reportFactory;
    }
}
