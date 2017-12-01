<?php

namespace HopitalNumerique\NotificationBundle\Enum;

/**
 * Class NotificationFrequencyEnum.
 */
abstract class NotificationFrequencyEnum
{
    /**
     * Notification frequency mode 'daily' (send notifications once per day).
     */
    const NOTIFICATION_FREQUENCY_DAILY = 'daily';

    /**
     * Notification frequency mode 'daily' (send notifications once per week).
     */
    const NOTIFICATION_FREQUENCY_WEEKLY = 'weekly';

    /**
     * Notification frequency mode 'daily' (send notifications immediately).
     */
    const NOTIFICATION_FREQUENCY_STRAIGHT = 'straight';

    /**
     * Notification frequency mode 'daily' (do not send notification).
     */
    const NOTIFICATION_FREQUENCY_OFF = 'off';

    /**
     * Return available notification frequencies.
     *
     * @return array
     */
    public static function getFrequencies()
    {
        return [
            self::NOTIFICATION_FREQUENCY_DAILY => self::NOTIFICATION_FREQUENCY_DAILY,
            self::NOTIFICATION_FREQUENCY_WEEKLY => self::NOTIFICATION_FREQUENCY_WEEKLY,
            self::NOTIFICATION_FREQUENCY_STRAIGHT => self::NOTIFICATION_FREQUENCY_STRAIGHT,
            self::NOTIFICATION_FREQUENCY_OFF => self::NOTIFICATION_FREQUENCY_OFF,
        ];
    }
}
