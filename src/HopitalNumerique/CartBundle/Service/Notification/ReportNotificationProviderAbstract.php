<?php

namespace HopitalNumerique\CartBundle\Service\Notification;

use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;

/**
 * Class ReportNotificationProviderAbstract.
 */
abstract class ReportNotificationProviderAbstract extends NotificationProviderAbstract
{
    const SECTION_CODE = 'report';

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }
}
