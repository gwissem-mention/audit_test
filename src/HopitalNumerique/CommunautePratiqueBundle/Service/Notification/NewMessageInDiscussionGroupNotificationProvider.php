<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\NotificationBundle\Entity\Notification;

/**
 * Class NewMessageInDiscussionGroupNotificationProvider.
 */
class NewMessageInDiscussionGroupNotificationProvider extends PracticeCommunityHelpGroupsNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'practice_community_new_message_in_discussion_group';

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
        return $this->subscriptionRepository->findSubscribersQueryBuilder(
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
        $this->processNotification(
            $message->getId(),
            $message->getDiscussion()->getTitle(),
            null,
            array_merge(parent::generateOptions($message->getDiscussion()->getGroups()->first(), null, $message->getDiscussion()), [
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
