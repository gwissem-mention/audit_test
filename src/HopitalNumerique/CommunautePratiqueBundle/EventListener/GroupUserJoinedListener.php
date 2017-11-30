<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Event\Group\UserJoinedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\GroupUserJoinedNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class GroupUserJoinedListener.
 *
 * @method GroupUserJoinedNotificationProvider getProvider()
 */
class GroupUserJoinedListener extends NotificationListenerAbstract
{
    /**
     * @param UserJoinedEvent $event
     */
    public function onGroupUserJoined(UserJoinedEvent $event)
    {
        $this->getProvider()->fire($event->getGroup(), $event->getRegistration());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::GROUP_USER_JOINED => 'onGroupUserJoined',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return GroupUserJoinedNotificationProvider::getNotificationCode();
    }
}
