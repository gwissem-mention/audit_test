<?php

namespace HopitalNumerique\ModuleBundle\Service\Notification;

use HopitalNumerique\ModuleBundle\Entity\Session;
use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\NotificationBundle\NotificationBundle;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Nodevo\RoleBundle\Entity\Role;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ComingTrainingSessionsNotificationProvider.
 */
class ComingTrainingSessionsNotificationProvider extends NotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'coming_training_sessions';

    const SECTION_CODE = 'anap_suggestion';

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
     * @param Session $session
     */
    public function fire(Session $session)
    {
        $now = new \DateTime();

        $moduleTitle = $session->getModuleTitre();
        if (strlen($moduleTitle) > NotificationBundle::LIMIT_NOTIFY_TITLE_LENGTH) {
            $moduleTitle = substr($moduleTitle, 0, NotificationBundle::LIMIT_NOTIFY_TITLE_LENGTH) . '...';
        }

        $sessionTitle = $session->getDescription();
        if (strlen($sessionTitle) > NotificationBundle::LIMIT_NOTIFY_DESC_LENGTH) {
            $sessionTitle = substr($sessionTitle, 0, NotificationBundle::LIMIT_NOTIFY_DESC_LENGTH) . '...';
        }

        $this->processNotification(
            $now->format('YmdHis'),
            $moduleTitle . ' ' . $session->getDateSessionString(),
            $sessionTitle . ' ' . $session->getFormateur()->getPrenomNom(),
            ['session' => $session]
        );
    }

    /**
     * Checks if a notification should be stacked for user.
     * Will return true if user role is authorized for training session.
     *
     * @param UserInterface $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function canNotify(UserInterface $user, Notification $notification)
    {
        $userRole = $user->getRoles()[0];

        /**
         * @var Role $sessionRole
         */
        $granted = false;
        foreach ($notification->getData('session')->getRestrictionAcces() as $sessionRole) {
            if ($userRole === $sessionRole->getRole()) {
                $granted = true;
                break;
            }
        }

        return $granted;
    }

    /**
     * @param UserInterface $user
     * @param Notification $notification
     */
    public function notify(UserInterface $user, Notification $notification)
    {

    }
}
