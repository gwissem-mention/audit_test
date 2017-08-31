<?php

namespace HopitalNumerique\ObjetBundle\EventListener;

use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;
use HopitalNumerique\ObjetBundle\Event\PublicationCommentedEvent;
use HopitalNumerique\ObjetBundle\Events;
use HopitalNumerique\ObjetBundle\Service\Notification\PublicationCommentedNotificationProvider;

/**
 * Class PublicationCommentedListener.
 *
 * @method PublicationCommentedNotificationProvider getProvider()
 */
class PublicationCommentedListener extends NotificationListenerAbstract
{
    /**
     * @param PublicationCommentedEvent $event
     */
    public function onPublicationCommented(PublicationCommentedEvent $event)
    {
        $this->getProvider()->fire($event->getComment(), $event->getObject(), $event->getInfradoc());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::PUBLICATION_COMMENTED => 'onPublicationCommented',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return PublicationCommentedNotificationProvider::getNotificationCode();
    }
}
