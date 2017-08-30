<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Event\EnrolmentEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\CommunityUserJoinedNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class CommunityUserJoinedListener.
 *
 * @method CommunityUserJoinedNotificationProvider getProvider()
 */
class CommunityUserJoinedListener extends NotificationListenerAbstract
{
    /**
     * @param EnrolmentEvent $event
     */
    public function onCommunityUserJoined(EnrolmentEvent $event)
    {
        $this->getProvider()->fire($event->getUser());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::ENROLL_USER => 'onCommunityUserJoined',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return CommunityUserJoinedNotificationProvider::getNotificationCode();
    }
}
