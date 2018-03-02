<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use Html2Text\Html2Text;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\NotificationBundle\Entity\Notification;

/**
 * Class PracticeCommunityHelpGroupsNotificationProviderAbstract.
 */
abstract class PracticeCommunityHelpGroupsNotificationProviderAbstract extends PracticeCommunityNotificationProviderAbstract
{
    const SECTION_CODE = 'practice_community_help_groups';

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }

    /**
     * @return integer
     */
    public static function getSectionPosition()
    {
        return 3;
    }

    /**
     * Returns users concerned by notification, in this case users who are active members of group.
     * notification date.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->groupeInscriptionRepository->getUsersInGroupQueryBuilder($notification->getData('groupId'));
    }
}
