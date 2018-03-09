<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;

/**
 * Class NewDiscussionNotificationProvider
 */
class NewDiscussionNotificationProvider extends PracticeCommunityPublicGroupsNotificationProviderAbstract
{
    const DEFAULT_FREQUENCY = NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_DAILY;

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
        return $this->userRepository->createCommunautePratiqueUsersQueryBuilder();
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Discussion $discussion
     */
    public function fire(Discussion $discussion)
    {
        $this->processNotification(
            $discussion->getId(),
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
        $this->mailManager->sendCdpNewDiscussionNotification($notification->getUser(), $notification->getData());
    }
}
