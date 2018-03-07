<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

/**
 * Class PracticeCommunityPublicGroupsNotificationProviderAbstract.
 */
abstract class PracticeCommunityPublicGroupsNotificationProviderAbstract extends PracticeCommunityNotificationProviderAbstract
{
    const SECTION_CODE = 'practice_community_public_groups';

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
        return 2;
    }
}
