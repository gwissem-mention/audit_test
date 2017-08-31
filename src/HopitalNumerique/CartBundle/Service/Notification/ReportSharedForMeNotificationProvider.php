<?php

namespace HopitalNumerique\CartBundle\Service\Notification;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ReportSharedForMeNotificationProvider.
 */
class ReportSharedForMeNotificationProvider extends ReportNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'report_shared_for_me';

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
     * @param User   $userFrom
     * @param User   $userTo
     */
    public function fire(Report $report, User $userFrom, User $userTo)
    {
        $this->processNotification(
            $report->getId(),
            $report->getName(),
            $userFrom->getPrenomNom(),
            [
                'userFrom' => $userFrom,
                'userTo' => $userTo,
            ]
        );
    }

    /**
     * Checks if a notification should be stacked for user.
     * Will return true if report is shared for given user.
     *
     * @param UserInterface $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function canNotify(UserInterface $user, Notification $notification)
    {
        return $notification->getData('userTo')->getId() === $user->getId();
    }

    /**
     * @param UserInterface $user
     * @param Notification $notification
     */
    public function notify(UserInterface $user, Notification $notification)
    {

    }
}
