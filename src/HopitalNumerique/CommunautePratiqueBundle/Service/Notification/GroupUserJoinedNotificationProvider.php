<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Inscription;
use HopitalNumerique\NotificationBundle\Model\Notification;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class GroupUserJoinedNotificationProvider.
 */
class GroupUserJoinedNotificationProvider extends PracticeCommunityNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'practice_community_group_user_joined';

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
     * @param Groupe $group
     * @param Inscription $registration
     */
    public function fire(Groupe $group, Inscription $registration)
    {
        $this->processNotification(
            [
                $group->getId(),
                $registration->getUser()->getId()
            ],
            $group->getTitre() . ' - ' . $registration->getUser()->getPrenomNom()
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
