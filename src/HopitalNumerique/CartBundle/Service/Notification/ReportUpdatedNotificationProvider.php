<?php

namespace HopitalNumerique\CartBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\NotificationBundle\Entity\Notification;
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
     * @return integer
     */
    public static function getNotifPosition()
    {
        return 2;
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
            $user->getPrenomNom(),
            array_merge(
                parent::generateOptions($report),
                [
                    'userFromId' => $user->getId()
                ]
            )
        );
    }

    /**
     * Returns users concerned by notification (origin or target of report shares).
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->reportSharingRepository->getSharingUsersFromReportQueryBuilder(
            $notification->getData('reportId'),
            [$notification->getData('userFromId')]
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        list($firstname, $lastname) = explode(' ', $notification->getDetail());
        $notification->addData('prenomUtilisateurDist', $firstname);
        $notification->addData('nomUtilisateurDist', $lastname);
        $this->mailManager->sendReportUpdated($notification->getUser(), $notification->getData());
    }
}
