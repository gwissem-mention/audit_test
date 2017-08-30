<?php

namespace HopitalNumerique\NotificationBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\NotificationBundle\Entity\Settings;

/**
 * Class UpdateNotificationSettingsHandler.
 */
class UpdateNotificationSettingsHandler
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * UpdateNotificationSettingsHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param UpdateNotificationSettingsCommand $notificationSettingsCommand
     */
    public function handle(UpdateNotificationSettingsCommand $notificationSettingsCommand)
    {
        $userSettings = new Settings($notificationSettingsCommand->notificationCode);
        $userSettings->setUserId($notificationSettingsCommand->userId);
        $userSettings->setFrequency($notificationSettingsCommand->frequency);
        $userSettings->setDetailLevel($notificationSettingsCommand->detailLevel);
        $userSettings->setScheduleDay($notificationSettingsCommand->scheduleDay);
        $userSettings->setScheduleHour($notificationSettingsCommand->scheduleHour);

        $this->entityManager->persist($userSettings);
        $this->entityManager->flush();
    }
}
