<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use Html2Text\Html2Text;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\MailBundle\Service\Traits\MailManagerAwareTrait;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use HopitalNumerique\PublicationBundle\Twig\PublicationExtension;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeInscriptionRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PracticeCommunityNotificationProviderAbstract.
 */
abstract class PracticeCommunityNotificationProviderAbstract extends NotificationProviderAbstract
{
    use MailManagerAwareTrait;

    /**
     * @var PublicationExtension $publicationExtension
     */
    protected $publicationExtension;

    /**
     * @var GroupeInscriptionRepository $groupeInscriptionRepository
     */
    protected $groupeInscriptionRepository;

    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * PracticeCommunityNotificationProviderAbstract constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @param PublicationExtension $publicationExtension
     * @param GroupeInscriptionRepository $groupeInscriptionRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        PublicationExtension $publicationExtension,
        GroupeInscriptionRepository $groupeInscriptionRepository,
        UserRepository $userRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage, $translator);
        $this->publicationExtension = $publicationExtension;
        $this->groupeInscriptionRepository = $groupeInscriptionRepository;
        $this->userRepository = $userRepository;
        $this->templatePath = '@HopitalNumeriqueCommunautePratique/notifications/' . $this::getNotificationCode() . '.html.twig';
    }

    /**
     * @param $comment
     *
     * @return string
     */
    public function processComment($comment)
    {
        return $this->publicationExtension->parsePublication($comment);
    }

    public function getSubscribers(Notification $notification)
    {
        return $this->userRepository->getCommunautePratiqueUsersQueryBuilder();
    }

    public function generateOptions(Groupe $group = null, User $user = null, Discussion $discussion = null)
    {
        $options = [];

        if (null !== $group) {
            $options = array_merge($options, [
                'groupId' => $group->getId(),
                'nomGroupe' => $group->getTitre(),
                'domainId' => $group->getDomains()->first()->getId(),
            ]);
        }

        if (null !== $user) {
            $options = array_merge($options, [
                'prenomUtilisateurDist' => $user->getFirstname(),
                'nomUtilisateurDist' => $user->getLastname(),
            ]);
        }

        if (null !== $discussion) {
            $options = array_merge($options, [
                'discussionId' => $discussion->getId(),
                'nomDiscussion' => $discussion->getTitle(),
            ]);
        }

        return $options;
    }
}
