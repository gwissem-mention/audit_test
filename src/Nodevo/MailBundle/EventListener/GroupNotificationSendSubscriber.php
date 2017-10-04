<?php

namespace Nodevo\MailBundle\EventListener;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Event\GroupedNotificationEvent;
use HopitalNumerique\NotificationBundle\Events;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use Nodevo\MailBundle\Manager\MailManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GroupNotificationSendSubscriber implements EventSubscriberInterface
{
    /**
     * @var MailManager $entityManager
     */
    protected $mailManager;

    /**
     * @var Notifications
     */
    protected $notificationService;

    /**
     * RecommendationSendedSubscriber constructor.
     *
     * @param MailManager $mailManager
     * @param Notifications $notifications
     */
    public function __construct(MailManager $mailManager, Notifications $notifications)
    {
        $this->mailManager = $mailManager;
        $this->notificationService = $notifications;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SEND_NOTIFICATION_GROUP => 'onSendNotificationGroup',
        ];
    }

    /**
     * @param GroupedNotificationEvent $event
     */
    public function onSendNotificationGroup(GroupedNotificationEvent $event)
    {
        /** @var Notification $notification */
        foreach ($event->getNotifications() as $notification) {
            $config = $this
                ->notificationService
                ->getProvider($notification->getNotificationCode())
                ->getConfigLabels();
            $sortedNotifications
            [$notification->getUser()->getId()]
            [$config->getSectionCode()]
            [$config->getNotificationCode()][] = $notification;
        }
        foreach ($sortedNotifications as $sortedNotification) {
            $this->mailManager->sendGroupedNotification($sortedNotification);
        }
    }
}
