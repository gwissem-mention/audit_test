<?php

namespace HopitalNumerique\CartBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Enum\NotificationDetailLevelEnum;
use HopitalNumerique\NotificationBundle\Event\GroupedNotificationEvent;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ReportSharedForOtherNotificationProvider.
 */
class ReportSharedForOtherNotificationProvider extends ReportNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'report_shared_for_other';

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
        return 4;
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param Report $report
     * @param User $userFrom
     * @param User $userTo
     */
    public function fire(Report $report, User $userFrom, User $userTo)
    {
        $this->processNotification(
            $report->getId(),
            $report->getName(),
            $userTo->getPrenomNom(),
            array_merge(
                parent::generateOptions($report, $userFrom),
                [
                    'userFromId' => $userFrom->getId(),
                    'userToId' => $userTo->getId(),
                ]
            )
        );
    }

    /**
     * Returns users concerned by notification (report owner in this case).
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->reportSharingRepository
            ->getSharingUsersFromReportQueryBuilder(
                $notification->getData('reportId'),
                $notification->getData('userFromId')
            );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        list($firstname, $lastname, $firstnameTo, $lastnameTo) = explode(' ', $notification->getDetail());
        $notification->addData('prenomUtilisateurDist', $firstname);
        $notification->addData('nomUtilisateurDist', $lastname);
        $notification->addData('prenomUtilisateurTo', $firstnameTo);
        $notification->addData('nomUtilisateurTo', $lastnameTo);
        $this->mailManager->sendReportSharedForOther($notification->getUser(), $notification->getData());
    }
}
