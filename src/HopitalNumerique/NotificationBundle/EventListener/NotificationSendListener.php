<?php

namespace HopitalNumerique\NotificationBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use HopitalNumerique\NotificationBundle\Event\NotificationEvent;
use HopitalNumerique\NotificationBundle\Events;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class NotificationSendListener
 */
class NotificationSendListener implements EventSubscriberInterface
{
    /**
     * @var Notifications
     */
    protected $notificationService;

    /**
     * NotificationSendListener constructor.
     *
     * @param Notifications $notifications
     */
    public function __construct(Notifications $notifications)
    {
        $this->notificationService = $notifications;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SEND_NOTIFICATION => 'onSendNotification'
        ];
    }

    /**
     * @param NotificationEvent $event
     */
    public function onSendNotification(NotificationEvent $event)
    {
        $provider = $this->notificationService->getProvider($event->getNotification()->getNotificationCode());
        $provider->notify($event->getNotification());
    }
}
