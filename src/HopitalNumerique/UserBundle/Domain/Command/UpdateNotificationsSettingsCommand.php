<?php

namespace HopitalNumerique\UserBundle\Domain\Command;

use HopitalNumerique\NotificationBundle\Entity\Settings;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class UpdateNotificationsSettingsCommand
 */
class UpdateNotificationsSettingsCommand
{
    /**
     * @var Settings[]
     */
    public $notificationsSettings;

    /**
     * @var boolean
     */
    public $publicationNotification;

    /**
     * @var boolean
     */
    public $activityNewsletter;

    /**
     * @var int
     */
    public $scheduleDay;

    /**
     * @var int
     */
    public $scheduleHour;

    /**
     * @var User
     */
    public $user;

    /**
     * UpdateNotificationsSettingsCommand constructor.
     *
     * @param User $user
     * @param Settings[] $notificationsSettings
     * @param array $schedules
     */
    public function __construct(User $user, array $notificationsSettings, array $schedules = null)
    {
        $this->user = $user;
        $this->publicationNotification = $user->getNotficationRequete();
        $this->activityNewsletter = $user->isActivityNewsletterEnabled();
        $this->notificationsSettings = $notificationsSettings;
        $this->scheduleDay = $schedules ? $schedules['scheduleDay'] : NotificationProviderAbstract::DEFAULT_SCHEDULE_DAY;
        $this->scheduleHour = $schedules ? $schedules['scheduleHour'] : NotificationProviderAbstract::DEFAULT_SCHEDULE_HOUR;
    }

}
