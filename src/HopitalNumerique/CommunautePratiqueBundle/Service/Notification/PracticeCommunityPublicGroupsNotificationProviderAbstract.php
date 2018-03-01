<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use Html2Text\Html2Text;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\MailBundle\Service\Traits\MailManagerAwareTrait;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use HopitalNumerique\PublicationBundle\Twig\PublicationExtension;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeInscriptionRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
