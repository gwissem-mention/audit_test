<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\NotificationBundle\Entity\Notification;

/**
 * Class NewDiscussionNotificationProvider
 */
class NewDiscussionNotificationProvider extends PracticeCommunityPublicGroupsNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'practice_community_new_discussion';

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
     * Returns users concerned by notification, in this case users who are subs to CDP.
     * notification date.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->userRepository->getCommunautePratiqueUsersQueryBuilder();
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Discussion $discussion
     */
    public function fire(Discussion $discussion)
    {
        $this->processNotification(
            [
                $discussion->getId(),
            ],
            $discussion->getTitle(),
            null,
            parent::generateOptions(null, null, $discussion)
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $this->mailManager->sendCdpGroupUserJoinedNotification($notification->getUser(), $notification->getData());
    }
}
