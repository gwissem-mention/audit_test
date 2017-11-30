<?php

namespace HopitalNumerique\AutodiagBundle\EventListener;

use HopitalNumerique\AutodiagBundle\Event\AutodiagUpdatePublishedEvent;
use HopitalNumerique\AutodiagBundle\Events;
use HopitalNumerique\AutodiagBundle\Service\Notification\AutodiagUpdatedNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class AutodiagUpdatePublishedListener.
 *
 * @method AutodiagUpdatedNotificationProvider getProvider()
 */
class AutodiagUpdatePublishedListener extends NotificationListenerAbstract
{
    /**
     * @param AutodiagUpdatePublishedEvent $event
     */
    public function onAutodiagUpdated(AutodiagUpdatePublishedEvent $event)
    {
        $this->getProvider()->fire($event->getAutodiag(), $event->getReason());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::AUTODIAG_UPDATE_PUBLISHED => 'onAutodiagUpdated',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return AutodiagUpdatedNotificationProvider::getNotificationCode();
    }
}
