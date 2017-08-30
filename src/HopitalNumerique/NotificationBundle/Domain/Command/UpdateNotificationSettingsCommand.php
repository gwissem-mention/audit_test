<?php

namespace HopitalNumerique\NotificationBundle\Domain\Command;

/**
 * Class UpdateNotificationSettingsCommand.
 */
class UpdateNotificationSettingsCommand
{
    /**
     * @var integer $userId
     */
    public $userId;

    /**
     * @var string $notificationCode
     */
    public $notificationCode;

    /**
     * @var string $frequency (See constants in NotificationBundle class)
     */
    public $frequency;

    /**
     * @var integer $detailLevel Level of detail needed (0 is lowest).
     */
    public $detailLevel;

    /**
     * @var integer $scheduleDay Day of week for sending notifications (1 to 7).
     */
    public $scheduleDay;

    /**
     * @var integer $scheduleHour Hour of day for sending notifications (0 to 23).
     */
    public $scheduleHour;

    /**
     * UpdateNotificationSettingsCommand constructor.
     *
     * @param integer      $userId
     * @param string       $notificationCode
     * @param string       $frequency
     * @param integer      $detailLevel
     * @param integer|null $scheduleDay
     * @param integer|null $scheduleHour
     */
    public function __construct(
        $userId,
        $notificationCode,
        $frequency,
        $detailLevel,
        $scheduleDay = null,
        $scheduleHour = null
    ) {
        $this->userId = $userId;
        $this->notificationCode = $notificationCode;
        $this->frequency = $frequency;
        $this->detailLevel = $detailLevel;
        $this->scheduleDay = $scheduleDay;
        $this->scheduleHour = $scheduleHour;
    }
}
