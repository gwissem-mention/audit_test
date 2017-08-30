<?php

namespace HopitalNumerique\ForumBundle\EventListener;

use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserPostEvent;
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
     * @param UserPostEvent $event
     */
    public function onPostCreatedSuccess(UserPostEvent $event)
    {
        $this->getProvider()->fire($event->getPost());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::POST_CREATE_SUCCESS => 'onPostCreatedSuccess',
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
