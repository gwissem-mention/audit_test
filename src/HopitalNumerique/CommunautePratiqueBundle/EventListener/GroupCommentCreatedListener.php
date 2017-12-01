<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Event\CommentCreatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\GroupCommentCreatedNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class GroupCommentCreatedListener.
 *
 * @method GroupCommentCreatedNotificationProvider getProvider()
 */
class GroupCommentCreatedListener extends NotificationListenerAbstract
{
    /**
     * @param CommentCreatedEvent $event
     */
    public function onGroupCommentCreated(CommentCreatedEvent $event)
    {
        $this->getProvider()->fire($event->getComment());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::GROUP_COMMENT_CREATED => 'onGroupCommentCreated',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return GroupCommentCreatedNotificationProvider::getNotificationCode();
    }
}
