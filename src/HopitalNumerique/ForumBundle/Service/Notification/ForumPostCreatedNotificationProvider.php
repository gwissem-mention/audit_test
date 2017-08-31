<?php

namespace HopitalNumerique\ForumBundle\Service\Notification;

use CCDNForum\ForumBundle\Entity\Post;
use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\NotificationBundle\NotificationBundle;
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
                NotificationBundle::LIMIT_NOTIFY_TITLE_LENGTH
            ),
            $this->processText(
                $post->getBody(),
                NotificationBundle::LIMIT_NOTIFY_DESC_LENGTH
            ),
            ['post' => $post]
        );
    }

    /**
     * Checks if a notification should be stacked for user.
     * Will return true in all cases.
     *
     * @param UserInterface $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function canNotify(UserInterface $user, Notification $notification)
    {
        return true;
    }

    /**
     * @param UserInterface $user
     * @param Notification $notification
     */
    public function notify(UserInterface $user, Notification $notification)
    {

    }
}
