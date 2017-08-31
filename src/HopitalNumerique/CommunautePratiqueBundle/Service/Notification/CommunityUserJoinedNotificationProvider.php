<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeInscriptionRepository;
use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\PublicationBundle\Twig\PublicationExtension;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CommunityUserJoinedNotificationProvider.
 */
class CommunityUserJoinedNotificationProvider extends PracticeCommunityNotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'practice_community_user_joined';

    /**
     * @var GroupeInscriptionRepository $groupRegisterRepo
     */
    protected $groupRegisterRepo;

    /**
     * CommunityUserJoinedNotificationProvider constructor.
     *
     * @param EventDispatcherInterface    $eventDispatcher
     * @param TokenStorageInterface       $tokenStorage
     * @param PublicationExtension        $publicationExtension
     * @param GroupeInscriptionRepository $groupRegisterRepo
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        PublicationExtension $publicationExtension,
        GroupeInscriptionRepository $groupRegisterRepo
    ) {
        $this->groupRegisterRepo = $groupRegisterRepo;
        parent::__construct($eventDispatcher, $tokenStorage, $publicationExtension);
    }

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
     * @param User $user
     */
    public function fire(User $user)
    {
        $title = $user->getPrenomNom();

        $tabGroups = [];
        foreach ($this->groupRegisterRepo->getUserGroups($user) as $group) {
            $tabGroups[] = $group->getTitre();
        }

        if (count($tabGroups)) {
            $title .= ' - ' . implode(' / ', $tabGroups);
        }

        $this->processNotification(
            $user->getId(),
            $title
        );
    }

    /**
     * Checks if a notification should be stacked for user.
     * Will return true in all cases.
     *
     * @param UserInterface $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function canNotify(UserInterface $user, Notification $notification)
    {
        return true;
    }

    /**
     * @param UserInterface $user
     * @param Notification $notification
     */
    public function notify(UserInterface $user, Notification $notification)
    {

    }
}
