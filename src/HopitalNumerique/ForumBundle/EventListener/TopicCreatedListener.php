<?php

namespace HopitalNumerique\ForumBundle\EventListener;

use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
use HopitalNumerique\ForumBundle\Event\PostEvent;
use HopitalNumerique\ForumBundle\Events;
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
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ForumEvents::USER_TOPIC_CREATE_COMPLETE => 'onTopicCreatedComplete',
            Events::POST_PUBLISHED => 'onPostPublished',
        ];
    }

    /**
     * Fires notification only if first post is not in waiting state (moderation)
     *
     * @param UserTopicEvent $event
     */
    public function onTopicCreatedComplete(UserTopicEvent $event)
    {
        $topic = $event->getTopic();

        if (count($topic->getPosts()) > 1 || !$topic->getFirstPost()->getEnAttente()) {
            $this->getProvider()->fire($topic);
        }
    }

    /**
     * Fires notification when first post is accepted after moderation
     *
     * @param PostEvent $event
     */
    public function onPostPublished(PostEvent $event)
    {
        $topic = $event->getPost()->getTopic();

        if (count($topic->getPosts()) === 1) {
            $this->getProvider()->fire($topic);
        }
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return ForumTopicCreatedNotificationProvider::getNotificationCode();
    }
}
