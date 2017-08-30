<?php

namespace HopitalNumerique\AutodiagBundle\Service\Notification;

use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntryRepository;
use HopitalNumerique\NotificationBundle\Model\Notification;
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
            ['autodiag' => $autodiag]
        );
    }

    /**
     * Checks if a notification should be stacked for user.
     * Will return true if publication user last view dateTime is older than notification dateTime.
     *
     * @param UserInterface $user
     * @param Notification $notification
     *
     * @return bool
     */
    public function canNotify(UserInterface $user, Notification $notification)
    {
        $lastSaveDate = $this->autodiagEntryRepository->getLastUserEntryUpdate(
            $user,
            $notification->getData('autodiag')
        );

        if (null === $lastSaveDate) {
            return false;
        } else {
            return new \DateTime($lastSaveDate) < $notification->getDateTime();
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
