<?php

namespace HopitalNumerique\CartBundle\Service\Notification;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ReportUpdatedNotificationProvider.
 */
class ReportUpdatedNotificationProvider extends ReportNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'report_updated';

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
     * @param Report $report
     * @param User   $user
     */
    public function fire(Report $report, User $user)
    {
        $this->processNotification(
            $report->getId(),
            $report->getName(),
            $user->getPrenomNom()
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
