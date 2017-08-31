<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\NotificationBundle\Model\Notification;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class GroupCreatedNotificationProvider.
 */
class GroupCreatedNotificationProvider extends PracticeCommunityNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'practice_community_group_created';

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
     */
    public function fire(Groupe $group)
    {
        $this->processNotification(
            $group->getId(),
            $group->getTitre(),
            $group->getDescriptionCourte() . ' - ' . $group->getDateDemarrage()->format('d/m/Y')
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
