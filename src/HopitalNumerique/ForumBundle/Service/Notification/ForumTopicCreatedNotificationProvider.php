<?php

namespace HopitalNumerique\ForumBundle\Service\Notification;

use CCDNForum\ForumBundle\Entity\Topic;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use Nodevo\MailBundle\Service\Traits\MailManagerAwareTrait;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ForumPostCreatedNotificationProvider.
 */
class ForumTopicCreatedNotificationProvider extends ForumNotificationProviderAbstract
{
    use MailManagerAwareTrait;

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
                static::getLimitNotifyTitleLength()
            ),
            $this->processText(
                $topic->getFirstPost()->getBody(),
                static::getLimitNotifyDetailLength()
            ),
            parent::generateOptions($topic, $topic->getFirstPost(), $topic->getId())
        );
    }

    /**
     * Returns users concerned by notification, in this case users who subscribed to board.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->subscriptionRepository->getBoardSubscribersQueryBuilder($notification->getData('id'));
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $this->mailManager->sendForumPostCreatedNotification($notification->getUser(), $notification->getData());
    }
}
