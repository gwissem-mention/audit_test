<?php

namespace HopitalNumerique\ObjetBundle\Service\Notification;

use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\ObjetBundle\Repository\ConsultationRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class PublicationNotificationProviderAbstract.
 */
abstract class PublicationNotificationProviderAbstract extends NotificationProviderAbstract
{
    const SECTION_CODE = 'publication';

    /**
     * @var ConsultationRepository $consultationRepository
     */
    protected $consultationRepository;

    /**
     * PublicationNotificationProviderAbstract constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param ConsultationRepository $consultationRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        ConsultationRepository $consultationRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage);
        $this->consultationRepository = $consultationRepository;
    }

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }
}
