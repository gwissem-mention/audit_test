<?php

namespace HopitalNumerique\CartBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\NotificationBundle\Entity\Notification;
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
            array_merge(
                parent::generateOptions($report, $userTo),
                [
                    'userFromId' => $userFrom->getId(),
                    'userToId' => $userTo->getId(),
                ]
            )
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
        $this->mailManager->sendReportSharedForMe($notification->getUser(), $notification->getData());
    }
}
