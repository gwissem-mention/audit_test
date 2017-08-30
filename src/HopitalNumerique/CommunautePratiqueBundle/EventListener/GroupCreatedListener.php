<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Event\GroupEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\GroupCreatedNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class GroupCreatedListener.
 *
 * @method GroupCreatedNotificationProvider getProvider()
 */
class GroupCreatedListener extends NotificationListenerAbstract
{
    /**
     * @param GroupEvent $event
     */
    public function onGroupCreated(GroupEvent $event)
    {
        $this->getProvider()->fire($event->getGroup());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::GROUP_CREATED => 'onGroupCreated',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return GroupCreatedNotificationProvider::getNotificationCode();
    }
}
