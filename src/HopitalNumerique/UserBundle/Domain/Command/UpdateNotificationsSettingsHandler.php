<?php

namespace HopitalNumerique\UserBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use HopitalNumerique\NotificationBundle\Domain\Command\UpdateNotificationSettingsCommand;
use HopitalNumerique\NotificationBundle\Domain\Command\UpdateNotificationSettingsHandler;

class UpdateNotificationsSettingsHandler
{
    /**
     * @var UserManagerInterface $userManager
     */
    protected $userManager;

    /**
     * @var UpdateNotificationSettingsHandler
     */
    protected $updNotifs;

    /**
     * UpdateNotificationsSettingsHandler constructor.
     *
     * @param UserManagerInterface $userManager
     * @param UpdateNotificationSettingsHandler $updNotifs
     */
    public function __construct(UserManagerInterface $userManager, UpdateNotificationSettingsHandler $updNotifs) {
        $this->userManager = $userManager;
        $this->updNotifs = $updNotifs;
    }

    public function handle(UpdateNotificationsSettingsCommand $command)
    {
        /** @var User $user */
        $user = $command->user;

        $user->setNotficationRequete($command->publicationNotification);
        $user->setActivityNewsletterEnabled($command->activityNewsletter);

        $this->userManager->updateUser($user);

        foreach ($command->notificationsSettings as $notification) {
            $notification->setScheduleDay($command->scheduleDay);
            $notification->setScheduleHour($command->scheduleHour);
            $this->updNotifs->handle(new UpdateNotificationSettingsCommand($notification));
        }
    }
}
