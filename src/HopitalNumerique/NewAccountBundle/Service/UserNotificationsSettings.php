<?php

namespace HopitalNumerique\NewAccountBundle\Service;

use HopitalNumerique\NotificationBundle\Entity\Settings;
use HopitalNumerique\NotificationBundle\Repository\SettingsRepository;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use HopitalNumerique\UserBundle\Entity\User;

class UserNotificationsSettings
{
    /**
     * @var SettingsRepository
     */
    private $settingsRepository;

    /**
     * @var Notifications
     */
    private $notifications;

    /**
     * UserNotificationsSettings constructor.
     *
     * @param SettingsRepository $settingsRepository
     * @param Notifications $notifications
     */
    public function __construct(SettingsRepository $settingsRepository, Notifications $notifications)
    {
        $this->settingsRepository = $settingsRepository;
        $this->notifications = $notifications;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function retrieveUserSettings(User $user)
    {
        $sections = $this->notifications->getStructuredProviders();
        $settings = $this->settingsRepository->findAllByUser($user);
        foreach ($sections as $section) {
            foreach ($section as $key => $notification) {
                if (!array_key_exists($key, $settings)) {
                    $settings[$key] = new Settings($key, false, $user->getId());
                }
            }
        }
        $schedules = $this->settingsRepository->findSchedulesByUser($user);

        return [$sections, $settings, $schedules];
    }
}
