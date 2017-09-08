<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\CartBundle\Entity\ReportSharing;

class RemoveSharingReportCommand
{
    /**
     * @var ReportSharing
     */
    public $sharing;

    /**
     * RemoveSharingReportCommand constructor.
     *
     * @param ReportSharing $reportSharing
     */
    public function __construct(ReportSharing $reportSharing)
    {
        $this->reportSharing = $reportSharing;
    }
}
