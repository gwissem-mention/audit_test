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
 * Class PracticeCommunityNotificationProviderAbstract.
 */
abstract class PracticeCommunityNotificationProviderAbstract extends NotificationProviderAbstract
{
    use MailManagerAwareTrait;

    const SECTION_CODE = 'practice_community';

    /**
     * @var PublicationExtension $publicationExtension
     */
    protected $publicationExtension;

    /**
     * @var GroupeInscriptionRepository $groupeInscriptionRepository
     */
    protected $groupeInscriptionRepository;

    /**
     * PracticeCommunityNotificationProviderAbstract constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @param PublicationExtension $publicationExtension
     * @param GroupeInscriptionRepository $groupeInscriptionRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        PublicationExtension $publicationExtension,
        GroupeInscriptionRepository $groupeInscriptionRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage, $translator);
        $this->publicationExtension = $publicationExtension;
        $this->groupeInscriptionRepository = $groupeInscriptionRepository;
        $this->templatePath = '@HopitalNumeriqueCommunautePratique/notifications/' . $this::getNotificationCode() . '.html.twig';
    }

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

    /**
     * @param $comment
     *
     * @return string
     */
    public function processComment($comment)
    {
        return $this->publicationExtension->parsePublication($comment);
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

    public function generateOptions(Groupe $group, User $user = null)
    {
        $options = [
            'groupId' => $group->getId(),
            'nomGroupe' => $group->getTitre(),
        ];
        if (null !== $user) {
            $options = array_merge($options, [
                'prenomUtilisateurDist' => $user->getFirstname(),
                'nomUtilisateurDist' => $user->getLastname(),
            ]);
        }

        return $options;
    }
}
