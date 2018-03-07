<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\SubscriptionRepository;
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
     * @var SubscriptionRepository $subscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var MessageRepository $messageRepository
     */
    protected $messageRepository;

    /**
     * PracticeCommunityNotificationProviderAbstract constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @param PublicationExtension $publicationExtension
     * @param GroupeInscriptionRepository $groupeInscriptionRepository
     * @param UserRepository $userRepository
     * @param SubscriptionRepository $subscriptionRepository
     * @param MessageRepository $messageRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        PublicationExtension $publicationExtension,
        GroupeInscriptionRepository $groupeInscriptionRepository,
        UserRepository $userRepository,
        SubscriptionRepository $subscriptionRepository,
        MessageRepository $messageRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage, $translator);
        $this->publicationExtension = $publicationExtension;
        $this->groupeInscriptionRepository = $groupeInscriptionRepository;
        $this->userRepository = $userRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->messageRepository = $messageRepository;
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

    public function generateOptions(Groupe $group = null, User $user = null, Discussion $discussion = null)
    {
        $options = [];

        if (null !== $group) {
            $options = [
                'groupId' => $group->getId(),
                'nomGroupe' => $group->getTitre(),
                'domainId' => $group->getDomains()->first()->getId(),
            ];
        }

        if (null !== $user) {
            $options = array_merge($options, [
                'prenomUtilisateurDist' => $user->getFirstname(),
                'nomUtilisateurDist' => $user->getLastname(),
            ]);
        }

        if (null !== $discussion) {
            $options = array_merge($options, [
                'discussion' => $discussion,
                'discussionId' => $discussion->getId(),
                'nomDiscussion' => $discussion->getTitle(),
            ]);
        }

        return $options;
    }
}
