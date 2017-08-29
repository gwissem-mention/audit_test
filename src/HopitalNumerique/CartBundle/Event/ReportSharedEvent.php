<?php

namespace HopitalNumerique\CartBundle\Event;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class ReportSharedEvent.
 */
class ReportSharedEvent extends ReportEvent
{
    /**
     * @var User $userFrom
     */
    protected $userFrom;

    /**
     * @var User $userTo
     */
    protected $userTo;

    /**
     * ReportUpdatedEvent constructor.
     *
     * @param Report $report
     * @param User   $userFrom
     * @param User   $userTo
     */
    public function __construct(Report $report, User $userFrom, User $userTo)
    {
        parent::__construct($report);
        $this->userFrom = $userFrom;
        $this->userTo = $userTo;
    }

    /**
     * @return User
     */
    public function getUserFrom()
    {
        return $this->userFrom;
    }

    /**
     * @return User
     */
    public function getUserTo()
    {
        return $this->userTo;
    }
}
