<?php

namespace HopitalNumerique\ForumBundle\Service\Notification;

use CCDNForum\ForumBundle\Entity\Topic;
use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\NotificationBundle\NotificationBundle;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ForumPostCreatedNotificationProvider.
 */
class ForumTopicCreatedNotificationProvider extends ForumNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'forum_topic_created';

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
     * @param Topic $topic
     */
    public function fire(Topic $topic)
    {
        $this->processNotification(
            $topic->getId(),
            $topic->getBoard()->getName() . ' - ' . $topic->getTitle() . ' - ' . $this->processText(
                $topic->getFirstPost()->getBody(),
                NotificationBundle::LIMIT_NOTIFY_TITLE_LENGTH
            ),
            $this->processText(
                $topic->getFirstPost()->getBody(),
                NotificationBundle::LIMIT_NOTIFY_DESC_LENGTH
            ),
            [
                'topic' => $topic,
                'firstPostId' => $topic->getFirstPost()->getId(),
            ]
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
