<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Inscription;
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
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Groupe $group
     * @param Inscription $registration
     */
    public function fire(Groupe $group, Inscription $registration)
    {
//        $this->processNotification(
//            [
//                $group->getId(),
//                $registration->getUser()->getId()
//            ],
//            $group->getTitre(),
//            $registration->getUser()->getPrenomNom(),
//            parent::generateOptions($group, $registration->getUser())
//        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
//        $this->mailManager->sendCdpGroupUserJoinedNotification($notification->getUser(), $notification->getData());
    }
}
