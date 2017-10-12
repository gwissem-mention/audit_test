<?php

namespace HopitalNumerique\AutodiagBundle\Service\Notification;

use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntryRepository;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Nodevo\MailBundle\Service\Traits\MailManagerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AutodiagUpdatedNotificationProvider.
 */
class AutodiagUpdatedNotificationProvider extends NotificationProviderAbstract
{
    use MailManagerAwareTrait;

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
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @param AutodiagEntryRepository $autodiagEntryRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        AutodiagEntryRepository $autodiagEntryRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage, $translator);
        $this->autodiagEntryRepository = $autodiagEntryRepository;
        $this->templatePath = '@HopitalNumeriqueAutodiag/notifications/' . $this::getNotificationCode() . '.html.twig';
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
     * @return integer
     */
    public static function getSectionPosition()
    {
        return 1;
    }

    /**
     * @return integer
     */
    public static function getNotifPosition()
    {
        return 1;
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
                'nomautodiag' => $autodiag->getTitle(),
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
        $notification->addData('miseAJour', $notification->getDetail());
        $this->mailManager->sendAutodiagUpdateNotification($notification->getUser(), $notification->getData());
    }
}
