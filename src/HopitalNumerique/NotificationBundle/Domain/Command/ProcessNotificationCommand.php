<?php

namespace HopitalNumerique\NotificationBundle\Domain\Command;

use HopitalNumerique\NotificationBundle\Entity\Notification;

/**
 * Class ProcessNotificationCommand.
 */
class ProcessNotificationCommand
{
    /**
     * @var Notification $notification
     */
    public $notification;

    /**
     * ProcessNotificationCommand constructor.
     *
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }
}
