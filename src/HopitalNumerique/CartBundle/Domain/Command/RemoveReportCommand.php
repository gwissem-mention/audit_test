<?php

namespace HopitalNumerique\CartBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Entity\Report;

class RemoveReportCommand
{
    /**
     * @var Report $report
     */
    public $report;

    /**
     * @var User $owner
     */
    public $owner;

    /**
     * RemoveReportCommand constructor.
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
