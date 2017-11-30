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
     * @var User $targetUser
     */
    protected $targetUser;

    /**
     * ReportUpdatedEvent constructor.
     *
     * @param Report $report
     * @param User   $user
     * @param User   $targetUser
     */
    public function __construct(Report $report, User $user, User $targetUser)
    {
        parent::__construct($report, $user);
        $this->targetUser = $targetUser;
    }

    /**
     * @return User
     */
    public function getTargetUser()
    {
        return $this->targetUser;
    }
}
