<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Event\Group\DocumentCreatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\GroupDocumentCreatedNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class GroupDocumentCreatedListener.
 *
 * @method GroupDocumentCreatedNotificationProvider getProvider()
 */
class GroupDocumentCreatedListener extends NotificationListenerAbstract
{
    /**
     * @param DocumentCreatedEvent $event
     */
    public function onGroupDocumentCreated(DocumentCreatedEvent $event)
    {
        $this->getProvider()->fire($event->getGroup(), $event->getDocument());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::GROUP_DOCUMENT_CREATED => 'onGroupDocumentCreated',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return GroupDocumentCreatedNotificationProvider::getNotificationCode();
    }
}
