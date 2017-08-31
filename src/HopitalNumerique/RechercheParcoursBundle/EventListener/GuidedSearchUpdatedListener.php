<?php

namespace HopitalNumerique\RechercheParcoursBundle\EventListener;

use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;
use HopitalNumerique\RechercheParcoursBundle\Event\GuidedSearchUpdatedEvent;
use HopitalNumerique\RechercheParcoursBundle\Events;
use HopitalNumerique\RechercheParcoursBundle\Service\Notification\GuidedSearchUpdatedNotificationProvider;

/**
 * Class GuidedSearchUpdatedListener.
 *
 * @method GuidedSearchUpdatedNotificationProvider getProvider()
 */
class GuidedSearchUpdatedListener extends NotificationListenerAbstract
{
    /**
     * @param GuidedSearchUpdatedEvent $event
     */
    public function onGuidedSearchUpdated(GuidedSearchUpdatedEvent $event)
    {
        $this->getProvider()->fire($event->getParcoursGestion(), $event->getReason());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::GUIDED_SEARCH_UPDATED => 'onGuidedSearchUpdated',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return GuidedSearchUpdatedNotificationProvider::getNotificationCode();
    }
}
