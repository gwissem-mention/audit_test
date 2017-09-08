<?php

namespace HopitalNumerique\NotificationBundle\Domain\Command;

use HopitalNumerique\NotificationBundle\Entity\Notification;

/**
 * Class SendNotificationCommand.
 */
class SendNotificationCommand
{
    /**
     * @var Notification[] $notifications
     */
    public $notifications;

    /**
     * ProcessNotificationCommand constructor.
     *
     * @param Notification[] $notifications
     */
    public function __construct(array $notifications)
    {
        $this->notifications = $notifications;
    }
}
