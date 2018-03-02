<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\DiscussionCreatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\MessageEvent;
use HopitalNumerique\CommunautePratiqueBundle\Event\Group\UserJoinedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\GroupUserJoinedNotificationProvider;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\NewDiscussionInGroupNotificationProvider;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\NewDiscussionNotificationProvider;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\NewMessageInDiscussionNotificationProvider;
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
            Events::DISCUSSION_CREATED => 'onDiscussionCreated',
            Events::DISCUSSION_MESSAGE_CREATED => 'onDiscussionMessageCreated',
            Events::DISCUSSION_CREATED_IN_GROUP => 'onDiscussionCreatedInGroup',
//            Events::DISCUSSION_MESSAGE_CREATED_IN_GROUP => 'onDiscussionMessageCreatedInGroup',
            Events::GROUP_USER_JOINED => 'onGroupUserJoined',
//            Events::DISCUSSION_MESSAGE_VALIDATED => 'onDiscussionMessageValidated',
        ];
    }

    /**
     * @param DiscussionCreatedEvent $event
     */
    public function onDiscussionCreated(DiscussionCreatedEvent $event)
    {
        $this->notificationService->getProvider(NewDiscussionNotificationProvider::getNotificationCode())->fire(
            $event->getDiscussion()
        );
    }

    /**
     * @param MessageEvent $event
     */
    public function onDiscussionMessageCreated(MessageEvent $event)
    {
        $this->notificationService->getProvider(NewMessageInDiscussionNotificationProvider::getNotificationCode())->fire(
            $event->getMessage()
        );
    }

    /**
     * @param DiscussionCreatedEvent $event
     */
    public function onDiscussionCreatedInGroup(DiscussionCreatedEvent $event)
    {
        $this->notificationService->getProvider(NewDiscussionInGroupNotificationProvider::getNotificationCode())->fire(
            $event->getDiscussion(),
            $event->getGroup()
        );
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
