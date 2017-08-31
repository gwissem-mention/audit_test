<?php

namespace HopitalNumerique\UserBundle\Service\Notification;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserRoleUpdatedNotificationProvider.
 */
class UserRoleUpdatedNotificationProvider extends NotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'user_role_updated';

    const SECTION_CODE = 'resource_user';

    /**
     * @return string
     */
    public static function getNotificationCode()
    {
        return self::NOTIFICATION_CODE;
    }

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param User $user
     */
    public function fire(User $user)
    {
        $this->processNotification($user->getId(), $user->getPrenomNom());
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
