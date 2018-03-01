<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Event\Group\UserJoinedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\GroupUserJoinedNotificationProvider;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class NotificationsListener.
 */
class NotificationsListener implements EventSubscriberInterface
{
    /**
     * @var Notifications $notificationService
     */
    protected $notificationService;

    /**
     * NotificationsListener constructor.
     *
     * @param Notifications $notificationService
     */
    public function __construct(Notifications $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::GROUP_USER_JOINED => 'onGroupUserJoined',
        ];
    }

    /**
     * @param UserJoinedEvent $event
     */
    public function onGroupUserJoined(UserJoinedEvent $event)
    {
        $this->notificationService->getProvider(GroupUserJoinedNotificationProvider::getNotificationCode())->fire(
            $event->getGroup(),
            $event->getRegistration()
        );
    }
}
