<?php

namespace HopitalNumerique\NewAccountBundle\Service;

use HopitalNumerique\NotificationBundle\Entity\Settings;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
use HopitalNumerique\NotificationBundle\Repository\SettingsRepository;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
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
            foreach ($section as $key => $provider) {
                if (!array_key_exists($key, $settings)) {
                    $setting = new Settings($key, $user->getId());
                    $setting->setFrequency($provider->getDefaultFrequency());
                    $settings[$key] = $setting;
                }
                foreach ($settings as $setting) {
                    if ($setting->getNotificationCode() === $key) {
                        $setting->setWanted($setting->getFrequency() !== NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_OFF);
                    }
                }
            }
        }
        $schedules = $this->settingsRepository->findSchedulesByUser($user);

        return [$sections, $settings, $schedules];
    }
}
