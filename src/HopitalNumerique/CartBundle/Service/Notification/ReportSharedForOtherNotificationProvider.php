<?php

namespace HopitalNumerique\CartBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\NotificationBundle\Entity\Notification;
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
            $userFrom->getPrenomNom().' '.$userTo->getPrenomNom(),
            array_merge(
                parent::generateOptions($report, $userFrom),
                [
                    'reportOwnerId' => $report->getOwner()->getId(),
                    'prenomUtilisateurDistTo' => $userTo->getFirstname(),
                    'nomUtilisateurDistTo' => $userTo->getLastname(),
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
        return $this->userRepository->getOneUserQueryBuilder($notification->getData('reportOwnerId'));
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $this->mailManager->sendReportSharedForOther($notification->getUser(), $notification->getData());
    }
}
