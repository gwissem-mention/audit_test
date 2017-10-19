<?php

namespace HopitalNumerique\ForumBundle\EventListener;

use HopitalNumerique\ForumBundle\Event\PostEvent;
use HopitalNumerique\ForumBundle\Events;
use HopitalNumerique\ForumBundle\Service\Notification\ForumPostCreatedNotificationProvider;
use HopitalNumerique\NotificationBundle\EventListener\NotificationListenerAbstract;

/**
 * Class PostCreatedListener.
 *
 * @method ForumPostCreatedNotificationProvider getProvider()
 */
class PostCreatedListener extends NotificationListenerAbstract
{
    /**
     * @param PostEvent $event
     */
    public function onPostPublished(PostEvent $event)
    {
        $this->getProvider()->fire($event->getPost());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::POST_PUBLISHED => 'onPostPublished',
        ];
    }

    /**
     * @return string
     */
    protected function getProviderCode()
    {
        return ForumPostCreatedNotificationProvider::getNotificationCode();
    }
}
