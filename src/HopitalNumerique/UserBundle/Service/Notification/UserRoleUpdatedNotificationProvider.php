<?php

namespace HopitalNumerique\UserBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use Nodevo\MailBundle\Service\Traits\MailManagerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class UserRoleUpdatedNotificationProvider.
 */
class UserRoleUpdatedNotificationProvider extends NotificationProviderAbstract
{
    use MailManagerAwareTrait;

    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    const NOTIFICATION_CODE = 'user_role_updated';

    const SECTION_CODE = 'resource_user';

    /**
     * UserRoleUpdatedNotificationProvider constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @param UserRepository $userRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        UserRepository $userRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage, $translator);
        $this->userRepository = $userRepository;
    }

    /**
     * @return string
     */
    public static function getNotificationCode()
    {
        return self::NOTIFICATION_CODE;
    }

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }

    /**
     * Submits notification to Notification manager service via FIRE_NOTIFICATION event.
     *
     * @param User $user
     */
    public function fire(User $user)
    {
        $this->processNotification(
            $user->getId(),
            $user->getPrenomNom(),
            null,
            [
                'regionId' => $user->getRegion()->getId(),
                'prenomUtilisateurDist' => $user->getFirstname(),
                'nomUtilisateurDist' => $user->getLastname(),
                'role' => $user->getRole(),
            ]
        );
    }

    /**
     * Returns users concerned by notification, in this case users located in same region.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->userRepository->getUsersByRegionQueryBuilder($notification->getData('regionId'));
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {
        $this->mailManager->sendUserRoleUpdateNotification($notification->getUser(), $notification->getData());
    }
}
