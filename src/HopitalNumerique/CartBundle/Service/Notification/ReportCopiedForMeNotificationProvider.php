<?php

namespace HopitalNumerique\CartBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ReportCopiedForMeNotificationProvider.
 */
class ReportCopiedForMeNotificationProvider extends ReportNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'report_copied_for_me';

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
     * @param Report $copiedReport
     * @param User   $userFrom
     * @param User   $userTo
     */
    public function fire(Report $copiedReport, User $userFrom, User $userTo)
    {
        $this->processNotification(
            $copiedReport->getId(),
            $copiedReport->getName(),
            $userFrom->getPrenomNom(),
            [
                'reportId' => $copiedReport->getId(),
                'userFromId' => $userFrom->getId(),
                'userToId' => $userTo->getId(),
            ]
        );
    }

    /**
     * Returns users concerned by notification (target user of sharing).
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->userRepository->getOneUserQueryBuilder($notification->getData('userToId'));
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {

    }
}
