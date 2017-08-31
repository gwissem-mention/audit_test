<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Notification;

use HopitalNumerique\NotificationBundle\Model\Notification;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\ObjetBundle\Repository\ConsultationRepository;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Repository\GuidedSearchRepository;
use HopitalNumerique\UserBundle\Entity\User;
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
            ['parcoursGestion' => $parcoursGestion]
        );
    }

    /**
     * Checks if a notification should be stacked for user.
     * Will return true if all of user matching guided searches have edit dateTime older than notification dateTime.
     *
     * @param UserInterface $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function canNotify(UserInterface $user, Notification $notification)
    {
        /**
         * @var User $user
         */
        $lastViewDate = $this->guidedSearchRepository->getUserLatestUpdateDate(
            $user,
            $notification->getData('parcoursGestion')
        );

        if (null === $lastViewDate) {
            return false;
        } else {
            return new \DateTime($lastViewDate) < $notification->getDateTime();
        }
    }

    /**
     * @param UserInterface $user
     * @param Notification $notification
     */
    public function notify(UserInterface $user, Notification $notification)
    {

    }
}
