<?php

namespace HopitalNumerique\NotificationBundle\Event;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class NotificationEvent.
 */
class NotificationEvent extends Event
{
    /**
     * @var Notification $notification
     */
    protected $notification;

    /**
     * NotificationEvent constructor.
     *
     * @param Notification  $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
