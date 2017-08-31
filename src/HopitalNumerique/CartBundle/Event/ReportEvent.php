<?php

namespace HopitalNumerique\CartBundle\Event;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\UserBundle\Entity\User;
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
     * @var Report $user;
     */
    protected $user;

    /**
     * ReportUpdatedEvent constructor.
     *
     * @param Report $report
     * @param User   $user
     */
    public function __construct(Report $report, User $user)
    {
        $this->report = $report;
        $this->user = $user;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
