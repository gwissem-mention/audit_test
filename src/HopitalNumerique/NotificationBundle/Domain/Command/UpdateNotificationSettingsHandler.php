<?php

namespace HopitalNumerique\NotificationBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\NotificationBundle\Entity\Settings;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;

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
        $notification = $notificationSettingsCommand->settings;
        if (false === $notification->isWanted()) {
            $notification->setFrequency(NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_OFF);
        }
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
