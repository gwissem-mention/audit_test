<?php

namespace HopitalNumerique\ForumBundle\Service\Notification;

use CCDNForum\ForumBundle\Entity\Post;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\NotificationBundle\Entity\Notification;

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
     * @return integer
     */
    public static function getNotifPosition()
    {
        return 1;
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Post $post
     */
    public function fire(Post $post)
    {
        $topic = $post->getTopic();
        $this->processNotification(
            $post->getId(),
            $topic->getTitle(),
            $this->processText(
                $post->getBody(),
                self::getLimitNotifyDetailLength()
            ),
            array_merge(
                parent::generateOptions($topic, $post),
                [
                    'boardId' => $topic->getBoard()->getId(),
                ]
            )
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
        return $this->subscriptionRepository->getTopicSubscribersQueryBuilder(
            $notification->getData('topicId'),
            $notification->getData('boardId')
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $notification
            ->addData('fildiscussion', $notification->getTitle())
            ->addData('message', $notification->getDetail())
        ;
        $this->mailManager->sendForumPostCreatedNotification($notification->getUser(), $notification->getData());
    }
}
