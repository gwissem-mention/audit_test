<?php

namespace HopitalNumerique\NotificationBundle\Service;

use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Entity\Settings;
use HopitalNumerique\NotificationBundle\Repository\SettingsRepository;
use HopitalNumerique\UserBundle\Repository\UserRepository;

/**
 * Class NotificationSubscriptionFinder.
 */
class NotificationSubscriptionFinder
{
    /**
     * @var SettingsRepository $settingsRepository
     */
    protected $settingsRepository;

    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * @var Notifications $notificationService
     */
    protected $notificationService;

    /**
     * NotificationSubscriptionFinder constructor.
     *
     * @param SettingsRepository $settingsRepository
     * @param UserRepository     $userRepository
     * @param Notifications      $notificationService
     */
    public function __construct(
        SettingsRepository $settingsRepository,
        UserRepository $userRepository,
        Notifications $notificationService
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->userRepository = $userRepository;
        $this->notificationService = $notificationService;
    }

    /**
     * Find users who are concerned by a notification and their notification settings.
     *
     * @param Notification $notification
     *
     * @return Settings[]
     */
    public function findSubscriptions(Notification $notification)
    {
        $notificationProvider = $this->notificationService->getProvider($notification->getNotificationCode());

        $usersQueryBuilder = $notificationProvider->getSubscribers($notification);

        if ($notification->getUser()) {
            $usersQueryBuilder
                ->andWhere('user.id = :limitUniqUserId')
                ->setParameter('limitUniqUserId', $notification->getUser()->getId());
        }

        if (null === $usersQueryBuilder) {
            $usersQueryBuilder = $this->userRepository->getActiveUsersQueryBuilder();
        }

        $providerSettings = [
            'frequency' => $notificationProvider::getDefaultFrequency(),
            'detailLevel' => (int)$notificationProvider::getDefaultDetailLevel(),
            'scheduleDay' => (int)$notificationProvider::getDefaultScheduleDay(),
            'scheduleHour' => (int)$notificationProvider::getDefaultScheduleHour(),
        ];

        return $this->settingsRepository->getSubscriptions(
            $notification->getNotificationCode(),
            $usersQueryBuilder,
            $providerSettings
        );
    }
}