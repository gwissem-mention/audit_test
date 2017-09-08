<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\Report;

class DuplicateReportCommand
{
    /**
     * @var Report $report
     */
    public $report;

    /**
     * @var string $reportName
     */
    public $reportName;

    /**
     * @var string $name
     */
    public $name;

    /**
     * @var User $owner
     */
    public $owner;

    /**
     * DuplicateReportCommand constructor.
     *
     * @param Report $report
     * @param User $owner
     */
    public function __construct(Report $report, User $owner)
    {
        $this->owner = $owner;
        $this->report = $report;
    }
}
