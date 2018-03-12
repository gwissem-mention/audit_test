<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\NotificationBundle\Entity\Notification;

/**
 * Class NewDiscussionInGroupNotificationProvider.
 */
class NewDiscussionInGroupNotificationProvider extends PracticeCommunityHelpGroupsNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'practice_community_new_discussion_in_group';

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
     * @param Discussion $discussion
     * @param Groupe $group
     */
    public function fire(Discussion $discussion, Groupe $group)
    {
        $this->processNotification(
            [
                $discussion->getId(),
                $group->getId(),
            ],
            $discussion->getTitle(),
            null,
            parent::generateOptions($group, null, $discussion)
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $this->mailManager->sendCdpNewDiscussionInGroupNotification($notification->getUser(), $notification->getData());
    }
}
