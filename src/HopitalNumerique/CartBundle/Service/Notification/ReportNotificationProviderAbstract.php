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
use Symfony\Component\Translation\TranslatorInterface;

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
     * @param TranslatorInterface $translator
     * @param ReportSharingRepository $reportSharingRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        ReportSharingRepository $reportSharingRepository,
        UserRepository $userRepository
    ) {
        parent::__construct($eventDispatcher, $tokenStorage, $translator);
        $this->reportSharingRepository = $reportSharingRepository;
        $this->userRepository = $userRepository;
        $this->templatePath = '@HopitalNumeriqueCart/notifications/'. $this::getNotificationCode() .'.html.twig';
    }

    /**
     * @return string
     */
    public static function getSectionCode()
    {
        return self::SECTION_CODE;
    }

    public function generateOptions(Report $report)
    {
        return [
            'nomRapport' => $report->getName(),
            'reportId' => $report->getId(),
        ];
    }
}
