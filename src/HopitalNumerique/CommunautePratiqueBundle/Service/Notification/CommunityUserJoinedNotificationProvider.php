<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CommunityUserJoinedNotificationProvider.
 */
class CommunityUserJoinedNotificationProvider extends PracticeCommunityNotificationProviderAbstract
{
    /**
    * Default frequency of this notification provider.
    */
    const DEFAULT_FREQUENCY = NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_WEEKLY;

    /**
     * Notification code.
     */
    const NOTIFICATION_CODE = 'practice_community_user_joined';

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
        return 5;
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param User $user
     */
    public function fire(User $user)
    {
        $title = $user->getPrenomNom();

        $this->processNotification(
            $user->getId(),
            $title,
            null,
            [
                'prenomUtilisateurDist' => $user->getFirstname(),
                'nomUtilisateurDist' => $user->getLastname(),
            ]
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
        return $this->groupeInscriptionRepository->getCommunityMembersQueryBuilder();
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $this->mailManager->sendCdpUserJoinedNotification($notification->getUser(), $notification->getData());
    }
}
