<?php

namespace HopitalNumerique\CartBundle\EventListener;

use HopitalNumerique\CartBundle\Event\ReportSharedEvent;
use HopitalNumerique\CartBundle\Events;
use HopitalNumerique\CartBundle\Service\Notification\ReportSharedForOtherNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class ReportSharedForOtherListener.
 *
 * @method ReportSharedForOtherNotificationProvider getProvider()
 */
class ReportSharedForOtherListener extends NotificationListenerAbstract
{
    /**
     * @param ReportSharedEvent $event
     */
    public function onReportShared(ReportSharedEvent $event)
    {
        $this->getProvider()->fire($event->getReport(), $event->getUser(), $event->getTargetUser());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::REPORT_SHARED => 'onReportShared',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return ReportSharedForOtherNotificationProvider::getNotificationCode();
    }
}
