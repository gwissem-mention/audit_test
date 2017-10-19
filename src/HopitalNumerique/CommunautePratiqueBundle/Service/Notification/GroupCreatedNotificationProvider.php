<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\NotificationBundle\Entity\Notification;
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
     * @return integer
     */
    public static function getNotifPosition()
    {
        return 4;
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
            $group->getDescriptionCourte() . ' - ' . $group->getDateDemarrage()->format('d/m/Y'),
            array_merge(
                parent::generateOptions($group),
                [
                    'dateDebut' => $group->getDateDemarrage()->format('d/m/Y'),
                ]
            )
        );
    }

    /**
     * Returns users concerned by notification, in this case all practice community members.
     * notification date.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->groupeInscriptionRepository->createCommunityMembersQueryBuilder([$notification->getData('domainId')]);
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $notification->addData('description', $notification->getDetail());
        $this->mailManager->sendCdpGroupCreatedNotification($notification->getUser(), $notification->getData());
    }
}
