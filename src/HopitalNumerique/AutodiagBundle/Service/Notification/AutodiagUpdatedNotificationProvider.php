<?php

namespace HopitalNumerique\AutodiagBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntryRepository;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

/**
 * Class AutodiagUpdatedNotificationProvider.
 */
class AutodiagUpdatedNotificationProvider extends NotificationProviderAbstract
{
    const NOTIFICATION_CODE = 'autodiag_update_published';

    const SECTION_CODE = 'autodiag';

    /**
     * @var AutodiagEntryRepository $autodiagEntryRepository
     */
    protected $autodiagEntryRepository;

    /**
     * AutodiagUpdatedNotificationProvider constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface    $tokenStorage
     * @param AutodiagEntryRepository  $autodiagEntryRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        AutodiagEntryRepository $autodiagEntryRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage);
        $this->autodiagEntryRepository = $autodiagEntryRepository;
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
     * @param Autodiag $autodiag
     * @param string   $reason
     */
    public function fire($autodiag, $reason)
    {
        $this->processNotification(
            [$autodiag->getId()],
            $autodiag->getTitle(),
            $reason,
            [
                'autodiagId' => $autodiag->getId(),
                'autodiagTitle' => $autodiag->getTitle()
            ]
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
        return $this->autodiagEntryRepository->getUpdatersBeforeQueryBuilder(
            $notification->getData('autodiagId'),
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
