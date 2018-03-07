<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\DiscussionCreatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\MessageEvent;
use HopitalNumerique\CommunautePratiqueBundle\Event\Discussion\MessageValidatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Event\Group\UserJoinedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\GroupUserJoinedNotificationProvider;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\NewDiscussionInGroupNotificationProvider;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\NewDiscussionNotificationProvider;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\NewMessageInDiscussionGroupNotificationProvider;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\NewMessageInDiscussionNotificationProvider;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\SubscriptionRepository;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\MailBundle\Manager\MailManager;
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
     * @var MessageRepository $messageRepository
     */
    protected $messageRepository;

    /**
     * @var SubscriptionRepository $subscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var MailManager $mailManager
     */
    protected $mailManager;

    /**
     * NotificationsListener constructor.
     *
     * @param Notifications $notificationService
     * @param MessageRepository $messageRepository
     * @param SubscriptionRepository $subscriptionRepository
     * @param MailManager $mailManager
     */
    public function __construct(
        Notifications $notificationService,
        MessageRepository $messageRepository,
        SubscriptionRepository $subscriptionRepository,
        MailManager $mailManager
    ) {
        $this->notificationService = $notificationService;
        $this->messageRepository = $messageRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->mailManager = $mailManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::DISCUSSION_CREATED => 'onDiscussionCreated',
            Events::DISCUSSION_MESSAGE_CREATED => 'onDiscussionMessageCreated',
            Events::GROUP_USER_JOINED => 'onGroupUserJoined',
            Events::DISCUSSION_MESSAGE_VALIDATED => 'onDiscussionMessageValidated',
        ];
    }

    /**
     * @param DiscussionCreatedEvent $event
     */
    public function onDiscussionCreated(DiscussionCreatedEvent $event)
    {
        if ($event->getGroup()) {
            $this->notificationService->getProvider(NewDiscussionInGroupNotificationProvider::getNotificationCode())->fire(
                $event->getDiscussion(),
                $event->getGroup()
            );
        } else {
            $this->notificationService->getProvider(NewDiscussionNotificationProvider::getNotificationCode())->fire(
                $event->getDiscussion()
            );
        }
    }

    /**
     * @param MessageEvent $event
     */
    public function onDiscussionMessageCreated(MessageEvent $event)
    {
        if (0 !== count($event->getMessage()->getDiscussion()->getGroups())) {
            $this->notificationService->getProvider(NewMessageInDiscussionGroupNotificationProvider::getNotificationCode())->fire(
                $event->getMessage()
            );
        } else {
            $this->notificationService->getProvider(NewMessageInDiscussionNotificationProvider::getNotificationCode())->fire(
                $event->getMessage()
            );
        }
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

    /**
     * @param MessageValidatedEvent $event
     */
    public function onDiscussionMessageValidated(MessageValidatedEvent $event)
    {
        $message = $event->getMessage();

        if (!$message->isPublished()) {
            return;
        }

        if (1 !== $this->messageRepository->countMessagesByDiscussion($message->getDiscussion())) {
            $subscribers = $this->subscriptionRepository->findSubscribers(ObjectIdentity::createFromDomainObject($message->getDiscussion()));
            $subscribers = array_filter($subscribers, function (User $user) use ($message) {
               return $user->getId() !== $message->getUser()->getId();
            });

            foreach ($subscribers as $subscriber) {
                $this->mailManager->sendCDPSubscriptionMail($message, $subscriber);
            }
        }
    }
}
