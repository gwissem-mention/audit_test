<?php

namespace HopitalNumerique\ObjetBundle\EventListener;

use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;
use HopitalNumerique\ObjetBundle\Event\PublicationNotifiedEvent;
use HopitalNumerique\ObjetBundle\Events;
use HopitalNumerique\ObjetBundle\Service\Notification\PublicationNotifiedNotificationProvider;

/**
 * Class PublicationNotifiedListener.
 *
 * @method PublicationNotifiedNotificationProvider getProvider()
 */
class PublicationNotifiedListener extends NotificationListenerAbstract
{
    /**
     * @param PublicationNotifiedEvent $event
     */
    public function onPublicationNotified(PublicationNotifiedEvent $event)
    {
        $this->getProvider()->fire($event->getObject(), $event->getInfradoc(), $event->getReason());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::PUBLICATION_NOTIFIED => 'onPublicationNotified',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return PublicationNotifiedNotificationProvider::getNotificationCode();
    }
}
