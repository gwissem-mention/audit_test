<?php

namespace HopitalNumerique\NotificationBundle\Event;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class GroupedNotificationEvent.
 */
class GroupedNotificationEvent extends Event
{
    /**
     * @var Notification[] $notifications
     */
    protected $notifications;

    /**
     * GroupedNotificationEvent constructor.
     *
     * @param Notification[] $notifications
     */
    public function __construct(array $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * @return Notification[]
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
