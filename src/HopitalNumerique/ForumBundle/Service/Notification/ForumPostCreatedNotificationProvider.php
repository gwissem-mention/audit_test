<?php

namespace HopitalNumerique\ForumBundle\Service\Notification;

use CCDNForum\ForumBundle\Entity\Post;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use Nodevo\MailBundle\DependencyInjection\MailManagerAwareTrait;

/**
 * Class ForumPostCreatedNotificationProvider.
 */
class ForumPostCreatedNotificationProvider extends ForumNotificationProviderAbstract
{
    use MailManagerAwareTrait;

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
        $topic = $post->getTopic();
        $this->processNotification(
            $post->getId(),
            $topic->getTitle() . ' - ' . $this->processText(
                $post->getBody(),
                self::getLimitNotifyTitleLength()
            ),
            $this->processText(
                $post->getBody(),
                self::getLimitNotifyDetailLength()
            ),
            array_merge(
                parent::generateOptions($topic, $post, $topic->getId()),
                [
                    'fildiscusssion' => $topic->getTitle()
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
        return $this->subscriptionRepository->getTopicSubscribersQueryBuilder($notification->getData('id'));
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $this->mailManager->sendForumPostCreatedNotification($notification->getUser(), $notification->getData());
    }
}
