<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\NotificationBundle\Entity\Notification;

/**
 * Class NewMessageInDiscussionNotificationProvider
 */
class NewMessageInDiscussionNotificationProvider extends PracticeCommunityPublicGroupsNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'practice_community_new_message_in_discussion';

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
        return 2;
    }

    /**
     * Returns users concerned by notification, in this case users who are subs to discussion.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->subscriptionRepository->createSubscribersQueryBuilder(
            ObjectIdentity::createFromDomainObject($notification->getData('discussion'))
        );
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Message $message
     */
    public function fire(Message $message)
    {
        $discussion = $message->getDiscussion();
        $this->processNotification(
            $message->getId(),
            $message->getDiscussion()->getTitle(),
            null,
            array_merge(parent::generateOptions(null, null, $discussion), [
                'discussion' => $discussion,
                'messageId' => $message->getId(),
            ])
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        /** @var Message $message */
        $message = $this->messageRepository->findOneById($notification->getData('messageId'));

        if (!$message->isPublished()) {
            return;
        }

        $this->mailManager->sendCdpNewMessageInDiscussionNotification($message, $notification->getUser());
    }
}
