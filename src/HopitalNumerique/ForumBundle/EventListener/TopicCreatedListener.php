<?php

namespace HopitalNumerique\ForumBundle\EventListener;

use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
use HopitalNumerique\ForumBundle\Service\Notification\ForumTopicCreatedNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class TopicCreatedListener.
 *
 * @method ForumTopicCreatedNotificationProvider getProvider()
 */
class TopicCreatedListener extends NotificationListenerAbstract
{
    /**
     * @param UserTopicEvent $event
     */
    public function onTopicCreatedComplete(UserTopicEvent $event)
    {
        $this->getProvider()->fire($event->getTopic());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ForumEvents::USER_TOPIC_CREATE_COMPLETE => 'onTopicCreatedComplete',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return ForumTopicCreatedNotificationProvider::getNotificationCode();
    }
}
