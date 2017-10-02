<?php

namespace HopitalNumerique\CartBundle\Service\Notification;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Repository\ReportSharingRepository;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use Nodevo\MailBundle\Service\Traits\MailManagerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ReportNotificationProviderAbstract.
 */
abstract class ReportNotificationProviderAbstract extends NotificationProviderAbstract
{
    use MailManagerAwareTrait;

    const SECTION_CODE = 'report';

    /**
     * @var ReportSharingRepository $reportSharingRepository
     */
    protected $reportSharingRepository;

    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * ReportNotificationProviderAbstract constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TokenStorageInterface $tokenStorage
     * @param ReportSharingRepository $reportSharingRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        ReportSharingRepository $reportSharingRepository,
        UserRepository $userRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage);
        $this->reportSharingRepository = $reportSharingRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }

    public function generateOptions(Report $report, User $user)
    {
        return [
            'nomRapport' => $report->getName(),
            'reportId' => $report->getId(),
            'prenomUtilisateurDist' => $user->getFirstname(),
            'nomUtilisateurDist' => $user->getLastname(),
        ];
    }
}
