<?php

namespace HopitalNumerique\CartBundle\Event;

use HopitalNumerique\CartBundle\Entity\Report;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ReportUpdatedEvent.
 */
class ReportEvent extends Event
{
    /**
     * @var Report $report
     */
    protected $report;

    /**
     * ReportUpdatedEvent constructor.
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }
}
