<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Event\CommentCreatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Service\Notification\FormCommentCreatedNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class FormCommentCreatedListener.
 *
 * @method FormCommentCreatedNotificationProvider getProvider()
 */
class FormCommentCreatedListener extends NotificationListenerAbstract
{
    /**
     * @param CommentCreatedEvent $event
     */
    public function onFormCommentCreated(CommentCreatedEvent $event)
    {
        $this->getProvider()->fire($event->getComment());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::FORM_COMMENT_CREATED => 'onFormCommentCreated',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return FormCommentCreatedNotificationProvider::getNotificationCode();
    }
}
