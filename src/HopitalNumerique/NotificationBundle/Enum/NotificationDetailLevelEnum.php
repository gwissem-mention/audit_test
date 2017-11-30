<?php

namespace HopitalNumerique\NotificationBundle\Enum;

/**
 * Class NotificationDetailLevelEnum.
 */
abstract class NotificationDetailLevelEnum
{
    /**
     * Notification detail low (title only).
     */
    const NOTIFICATION_DETAIL_LEVEL_LOW = 0;

    /**
     * Notification detail low (title and detail).
     */
    const NOTIFICATION_DETAIL_LEVEL_NORMAL = 1;

    /**
     * Return available notification detail levels.
     *
     * @return array
     */
    public static function getDetailLevels()
    {
        return [
            self::NOTIFICATION_DETAIL_LEVEL_LOW => self::NOTIFICATION_DETAIL_LEVEL_LOW,
            self::NOTIFICATION_DETAIL_LEVEL_NORMAL => self::NOTIFICATION_DETAIL_LEVEL_NORMAL,
        ];
    }
}
