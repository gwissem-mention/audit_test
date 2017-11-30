<?php

namespace HopitalNumerique\NotificationBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use HopitalNumerique\NotificationBundle\Service\NotificationScheduleDateCalculator;
use HopitalNumerique\NotificationBundle\Service\NotificationSubscriptionFinder;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Repository\UserRepository;

/**
 * Class ProcessNotificationHandler.
 */
class ProcessNotificationHandler
{
    /**
     * Max notifications in a single insert for notification saving batch.
     */
    const NOTIFICATION_SAVE_BATCH_SIZE = 100;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var NotificationSubscriptionFinder $notificationSubscriptionFinder
     */
    protected $notificationSubscriptionFinder;

    /**
     * @var Notifications $notificationService
     */
    protected $notificationService;

    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * @var NotificationScheduleDateCalculator $dateCalculator
     */
    protected $dateCalculator;

    /**
     * ProcessNotificationHandler constructor.
     *
     * @param EntityManagerInterface             $entityManager
     * @param NotificationSubscriptionFinder     $notificationSubscriptionFinder
     * @param Notifications                      $notificationService
     * @param UserRepository                     $userRepository
     * @param NotificationScheduleDateCalculator $dateCalculator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationSubscriptionFinder $notificationSubscriptionFinder,
        Notifications $notificationService,
        UserRepository $userRepository,
        NotificationScheduleDateCalculator $dateCalculator
    ) {
        $this->entityManager = $entityManager;
        $this->notificationSubscriptionFinder = $notificationSubscriptionFinder;
        $this->notificationService = $notificationService;
        $this->userRepository = $userRepository;
        $this->dateCalculator = $dateCalculator;
    }

    /**
     * @param ProcessNotificationCommand $notificationCommand
     */
    public function handle(ProcessNotificationCommand $notificationCommand)
    {
        //We must get the users concerned by this notification.
        $subscriptions = $this->notificationSubscriptionFinder->findSubscriptions($notificationCommand->notification);

        if ($notificationCommand->notification->getUser()) {
            $authorId = $notificationCommand->notification->getUser()->getId();
        } else {
            $authorId = null;
        }

        //Read results and persist notifications.
        $batchSize = self::NOTIFICATION_SAVE_BATCH_SIZE;
        $i = 0;
        foreach ($subscriptions as $subscription) {
            if ($i === 0) {
                $this->dateCalculator->initNow();
            }

            //Do no send notification to user who initiated it.
            if ($authorId === $subscription->getUserId()) {
                continue;
            }

            //Complete notification object with user, frequency and detail level.
            $notification = clone($notificationCommand->notification);

            $notification->setUser($this->entityManager->getReference(User::class, $subscription->getUserId()));
            $notification->setFrequency($subscription->getFrequency());
            $notification->setDetailLevel($subscription->getDetailLevel());

            //Retrieve notification date depending on settings and set it.
            $notification->setScheduledFor($this->dateCalculator->calculateScheduleDateTime(
                $notificationCommand->notification,
                $subscription
            ));

            $this->entityManager->persist($notification);

            if ((++$i % $batchSize) === 0) {
                $this->entityManager->flush();
            }
        }

        //Persist possible last rows.
        $this->entityManager->flush();

        //At this stage, the background process responsible for unstacking notifications will take over.
    }
}
