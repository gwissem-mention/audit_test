<?php

namespace HopitalNumerique\ForumBundle\Service\Notification;

use CCDNForum\ForumBundle\Entity\Post;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ForumPostCreatedNotificationProvider.
 */
class ForumPostCreatedNotificationProvider extends ForumNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'forum_post_created';

    /**
     * @return string
     */
    public static function getNotificationCode()
    {
        return self::NOTIFICATION_CODE;
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Post $post
     */
    public function fire(Post $post)
    {
        $this->processNotification(
            $post->getId(),
            $post->getTopic()->getTitle() . ' - ' . $this->processText(
                $post->getBody(),
                self::getLimitNotifyTitleLength()
            ),
            $this->processText(
                $post->getBody(),
                self::getLimitNotifyDetailLength()
            ),
            ['topicId' => $post->getTopic()->getId()]
        );
    }

    /**
     * Returns users concerned by notification, in this case users who subscribed to topic.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->subscriptionRepository->getTopicSubscribersQueryBuilder($notification->getData('topicId'));
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {

    }
}
