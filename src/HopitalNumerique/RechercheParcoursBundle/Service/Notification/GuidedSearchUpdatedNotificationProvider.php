<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Notification;

use \Doctrine\ORM\QueryBuilder;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\ObjetBundle\Repository\ConsultationRepository;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Repository\GuidedSearchRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class GuidedSearchUpdatedNotificationProvider.
 */
class GuidedSearchUpdatedNotificationProvider extends NotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'guided_search_updated';

    const SECTION_CODE =  'guided_search';

    /**
     * @var ConsultationRepository $guidedSearchRepository
     */
    protected $guidedSearchRepository;

    /**
     * GuidedSearchUpdatedNotificationProvider constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param GuidedSearchRepository $guidedSearchRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        GuidedSearchRepository $guidedSearchRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage);
        $this->guidedSearchRepository = $guidedSearchRepository;
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
     * @param RechercheParcoursGestion $parcoursGestion
     * @param string                   $reason
     */
    public function fire(RechercheParcoursGestion $parcoursGestion, $reason)
    {
        $this->processNotification(
            $parcoursGestion->getId(),
            $parcoursGestion->getNom(),
            $reason,
            ['parcoursGestionId' => $parcoursGestion->getId()]
        );
    }

    /**
     * Returns users concerned by notification, in this case users whose last entry update for autodiag was before
     * notification date.
     *
     * @param Notification $notification
     *
     * @return QueryBuilder
     */
    public function getSubscribers(Notification $notification)
    {
        return $this->guidedSearchRepository->getUpdatersBeforeQueryBuilder(
            $notification->getData('parcoursGestionId'),
            $notification->getCreatedAt()
        );
    }

    /**
     * @param Notification $notification
     */
    public function notify(Notification $notification)
    {

    }
}
