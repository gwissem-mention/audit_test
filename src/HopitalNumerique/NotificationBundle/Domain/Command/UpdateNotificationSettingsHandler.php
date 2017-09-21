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
        $notification = $notificationSettingsCommand->settings;
        if ($notification->isWanted()) {
            $this->entityManager->persist($notification);
        } elseif (null !== $notification->getId()) {
            $this->entityManager->remove($notification);
        }

        $this->entityManager->flush();
    }
}
