<?php

namespace HopitalNumerique\NotificationBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
use HopitalNumerique\NotificationBundle\Event\GroupedNotificationEvent;
use HopitalNumerique\NotificationBundle\Event\NotificationEvent;
use HopitalNumerique\NotificationBundle\Events;
use HopitalNumerique\NotificationBundle\Repository\NotificationRepository;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use HopitalNumerique\NotificationBundle\Service\NotificationSubscriptionFinder;
use Nodevo\ToolsBundle\Tools\Arrays\BreakIterator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class SendNotificationHandler.
 */
class SendNotificationHandler
{
    /**
     * Max notifications in a single insert for notification saving batch.
     */
    const NOTIFICATION_SAVE_BATCH_SIZE = 100;

    /**
     * @var NotificationRepository $notificationRepository
     */
    protected $notificationRepository;

    /**
     * @var Notifications $notificationService
     */
    protected $notificationService;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var NotificationSubscriptionFinder $subscriptionFinder
     */
    protected $subscriptionFinder;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * SendNotificationHandler constructor.
     *
     * @param NotificationRepository $notificationRepository
     * @param Notifications $notificationService
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationSubscriptionFinder $subscriptionFinder
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        NotificationRepository $notificationRepository,
        Notifications $notificationService,
        EventDispatcherInterface $eventDispatcher,
        NotificationSubscriptionFinder $subscriptionFinder,
        EntityManagerInterface $entityManager
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->notificationService = $notificationService;
        $this->eventDispatcher = $eventDispatcher;
        $this->subscriptionFinder = $subscriptionFinder;
        $this->entityManager = $entityManager;
    }

    /**
     * @param SendNotificationCommand $notificationCommand
     */
    public function handle(SendNotificationCommand $notificationCommand)
    {
//        //This is used to stop more easily on values change while reading an array.
//        $iterator = new BreakIterator($notificationCommand->notifications);
//
//        $stack = [];
//
//        //We're interested in user and frequency changes (therefore data must be ordered by user and frequency).
//        $iterator
//            ->addChangeTracker('user', function (Notification $notification) {
//                return $notification->getUser()->getId();
//            })
//            ->addChangeTracker('isStraight', function (Notification $notification) {
//                return NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_STRAIGHT === $notification->getFrequency();
//            })
//        ;
//
//        foreach ($iterator as $notification) {
//            //Check if there is a break
//            if ($iterator->hasChanged('user') || $iterator->hasChanged('isStraight')) {
//                //Unstack
//                $this->processStack($stack, $iterator->getPreviousValue('isStraight'));
//                $stack = [];
//            }
//            $stack[] = $notification;
//        }

//        dump($stack,$iterator);die;
        //We have to process last item(s) since last (and maybe several others) could not be caught by change tracker.
//        $this->processStack($stack, $iterator->getPreviousValue('isStraight'));
        $this->processStack($notificationCommand->notifications);
    }

    /**
     * @param Notification[] $notifications
     */
    public function processStack($notifications)
    {
        foreach ($notifications as $key => $notification) {
            if ($notification->getFrequency() === NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_STRAIGHT) {
                $notificationEvent = new NotificationEvent($notification);
                $this->eventDispatcher->dispatch(Events::SEND_NOTIFICATION, $notificationEvent);

                //Delete notification
                $this->entityManager->remove($notification);
                unset($notifications[$key]);
            }
        }

        $subscriptionFinder = $this->subscriptionFinder;
        $groupedNotifications = array_filter(
            $notifications,
            function (Notification $notification) use ($subscriptionFinder) {
                $subscribers = $subscriptionFinder->findSubscriptions($notification);

                if (count($subscribers) && $subscribers[0]->getUserId() === $notification->getUser()->getId()) {
                    return true;
                }
                $this->entityManager->remove($notification);
                return false;
            }
        );

        $userNotificationMap = [];
        foreach ($groupedNotifications as $notification) {
            $userNotificationMap[$notification->getUser()->getId()][] = $notification;
        }

        foreach ($userNotificationMap as $userNotifications) {
            //Send with GroupedNotificationEvent event.
            //Specific actions like sending email must be handled in event listeners.
            $notificationEvent = new GroupedNotificationEvent($userNotifications[0]->getUser(), $userNotifications);
            $this->eventDispatcher->dispatch(
                Events::SEND_NOTIFICATION_GROUP,
                $notificationEvent
            );
        }

        //Delete notifications
        foreach ($notifications as $notification) {
            $this->entityManager->remove($notification);
        }

        $this->entityManager->flush();
    }

        /**
         * Processing and deleting an array of notifications, differentiated by whether frequency is 'straight' or not.
         *
         * @param array $notifications
         * @param $isStraight
         */
//    protected function processStack(array $notifications, $isStraight)
//    {
//        if ($isStraight) {
//            //Straight : do not filter, and call notify method of notification provider responsible for each
//            //notification to process..
//
//            foreach ($notifications as $notification) {
//                //Send with NotificationEvent event.
//                //Specific actions like sending email must be handled in event listeners.
//                $notificationEvent = new NotificationEvent($notification);
//                $this->eventDispatcher->dispatch(Events::SEND_NOTIFICATION, $notificationEvent);
//
//                //Delete notification
//                $this->entityManager->remove($notification);
//                $this->entityManager->flush();
//            }
//        } else {
//            //Daily / weekly : we do not call providers' notify function, instead we throw an event for all
//            //notifications.
//
//            //First filter the notifications
//            /**
//             * Removes notifications that should not be sent because of a subscription change.
//             * Notification settings are ignored here, which means setting notification to 'off' on monday will
//             * not prevent Tuesday notifications to be sent as long as they've been persisted before settings change.
//             */
//            $subscriptionFinder = $this->subscriptionFinder;
//            $notifications = array_filter(
//                $notifications,
//                function (Notification $notification) use ($subscriptionFinder) {
//                    $subscribers = $subscriptionFinder->findSubscriptions($notification);
//                    return count($subscribers) && $subscribers[0]->getUserId() === $notification->getUser()->getId();
//                }
//            );
//
//            //Send with GroupedNotificationEvent event.
//            //Specific actions like sending email must be handled in event listeners.
//            if (count($notifications)) {
//                $notificationEvent = new GroupedNotificationEvent($notifications);
//                $this->eventDispatcher->dispatch(
//                    Events::SEND_NOTIFICATION_GROUP,
//                    $notificationEvent
//                );
//
//                //Delete notifications
//                foreach ($notifications as $notification) {
//                    $this->entityManager->remove($notification);
//                }
//                $this->entityManager->flush();
//            }
//        }
//    }
    }
