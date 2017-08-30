<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeInscriptionRepository;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\PublicationBundle\Twig\PublicationExtension;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Html2Text\Html2Text;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PracticeCommunityNotificationProviderAbstract.
 */
abstract class PracticeCommunityNotificationProviderAbstract extends NotificationProviderAbstract
{
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
     * @param PublicationExtension $publicationExtension
     * @param GroupeInscriptionRepository $groupeInscriptionRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        PublicationExtension $publicationExtension,
        GroupeInscriptionRepository $groupeInscriptionRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage);
        $this->publicationExtension = $publicationExtension;
        $this->groupeInscriptionRepository = $groupeInscriptionRepository;
    }

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }

    /**
     * @param $comment
     * @param $limit
     *
     * @return string
     */
    public function processComment($comment, $limit)
    {
        //Parse custom publication tags
        $htmlText = $this->publicationExtension->parsePublication($comment);

        //Remove HTML code
        $htmlToPdf = new Html2Text($htmlText, ['do_links' => 'none', 'width' => 0]);
        $cleanText = $htmlToPdf->getText();

        //Truncate and return
        return mb_strimwidth($cleanText, 0, $limit, '...');
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
