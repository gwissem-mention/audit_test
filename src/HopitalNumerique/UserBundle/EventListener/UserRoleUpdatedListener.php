<?php

namespace HopitalNumerique\UserBundle\EventListener;

use HopitalNumerique\UserBundle\Event\UserRoleUpdatedEvent;
use HopitalNumerique\UserBundle\Service\Notification\UserRoleUpdatedNotificationProvider;
use HopitalNumerique\UserBundle\UserEvents;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class UserRoleUpdatedListener.
 *
 * @method UserRoleUpdatedNotificationProvider getProvider()
 */
class UserRoleUpdatedListener extends NotificationListenerAbstract
{
    const ROLES_TO_BE_NOTIFIED = ['ROLE_AMBASSADEUR_7', 'ROLE_EXPERT_6'];

    /**
     * @param UserRoleUpdatedEvent $event
     */
    public function onUserRoleUpdated(UserRoleUpdatedEvent $event)
    {
        if (in_array($event->getUser()->getRole(), self::ROLES_TO_BE_NOTIFIED)) {
            $this->getProvider()->fire($event->getUser());
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserEvents::USER_ROLE_UPDATED => 'onUserRoleUpdated',
        ];
    }

    /**
    * @return string
    */
    protected function getProviderCode()
    {
        return UserRoleUpdatedNotificationProvider::getNotificationCode();
    }
}
