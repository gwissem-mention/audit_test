<?php

namespace HopitalNumerique\NotificationBundle\Enum;

/**
 * Class NotificationDayEnum.
 */
abstract class NotificationDayEnum
{
    /**
     * Notification day monday.
     */
    const NOTIFICATION_DAY_MONDAY = 1;

    /**
     * Notification day tuesday.
     */
    const NOTIFICATION_DAY_TUESDAY = 2;

    /**
     * Notification day wednesday.
     */
    const NOTIFICATION_DAY_WEDNESDAY = 3;

    /**
     * Notification day thursday.
     */
    const NOTIFICATION_DAY_THURSDAY = 4;

    /**
     * Notification day friday.
     */
    const NOTIFICATION_DAY_FRIDAY = 5;

    /**
     * Notification day saturday.
     */
    const NOTIFICATION_DAY_SATURDAY = 6;

    /**
     * Notification day sunday.
     */
    const NOTIFICATION_DAY_SUNDAY = 7;

    /**
     * Return available notification days.
     *
     * @return array
     */
    public static function getNotificationDays()
    {
        return [
            self::NOTIFICATION_DAY_MONDAY => self::NOTIFICATION_DAY_MONDAY,
            self::NOTIFICATION_DAY_TUESDAY => self::NOTIFICATION_DAY_TUESDAY,
            self::NOTIFICATION_DAY_WEDNESDAY => self::NOTIFICATION_DAY_WEDNESDAY,
            self::NOTIFICATION_DAY_THURSDAY => self::NOTIFICATION_DAY_THURSDAY,
            self::NOTIFICATION_DAY_FRIDAY => self::NOTIFICATION_DAY_FRIDAY,
            self::NOTIFICATION_DAY_SATURDAY => self::NOTIFICATION_DAY_SATURDAY,
            self::NOTIFICATION_DAY_SUNDAY => self::NOTIFICATION_DAY_SUNDAY,
        ];
    }

    /**
     * Return day names usable in date relative format.
     *
     * @return string[] array of days indexed by day number (1 to 7)
     */
    public static function getDayNames()
    {
        return  [
            self::NOTIFICATION_DAY_MONDAY => 'Monday',
            self::NOTIFICATION_DAY_TUESDAY => 'Tuesday',
            self::NOTIFICATION_DAY_WEDNESDAY => 'Wednesday',
            self::NOTIFICATION_DAY_THURSDAY => 'Thursday',
            self::NOTIFICATION_DAY_FRIDAY => 'Friday',
            self::NOTIFICATION_DAY_SATURDAY => 'Saturday',
            self::NOTIFICATION_DAY_SUNDAY => 'Sunday',
        ];
    }
}
