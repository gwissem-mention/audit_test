<?php

namespace HopitalNumerique\NotificationBundle\Event;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class GroupedNotificationEvent.
 */
class GroupedNotificationEvent extends Event
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Notification[] $notifications
     */
    protected $notifications;

    /**
     * GroupedNotificationEvent constructor.
     *
     * @param Notification[] $notifications
     */
    public function __construct(User $user, array $notifications)
    {
        $this->user= $user;
        $this->notifications = $notifications;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Notification[]
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
