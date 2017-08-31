<?php

namespace HopitalNumerique\CartBundle\EventListener;

use HopitalNumerique\CartBundle\Event\ReportEvent;
use HopitalNumerique\CartBundle\Events;
use HopitalNumerique\CartBundle\Service\Notification\ReportUpdatedNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class ReportUpdatedListener.
 *
 * @method ReportUpdatedNotificationProvider getProvider()
 */
class ReportUpdatedListener extends NotificationListenerAbstract
{
    /**
     * @param ReportEvent $event
     */
    public function onReportUpdated(ReportEvent $event)
    {
        $this->getProvider()->fire($event->getReport(), $event->getUser());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::REPORT_UPDATED => 'onReportUpdated',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return ReportUpdatedNotificationProvider::getNotificationCode();
    }
}
