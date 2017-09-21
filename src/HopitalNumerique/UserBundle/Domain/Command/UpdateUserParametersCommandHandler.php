<?php

namespace HopitalNumerique\UserBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use HopitalNumerique\NotificationBundle\Domain\Command\UpdateNotificationSettingsCommand;
use HopitalNumerique\NotificationBundle\Domain\Command\UpdateNotificationSettingsHandler;

class UpdateUserParametersCommandHandler
{
    /**
     * @var TokenStorage $tokenStorage
     */
    protected $tokenStorage;

    /**
     * @var UserManagerInterface $userManager
     */
    protected $userManager;

    /**
     * @var UpdateNotificationSettingsHandler
     */
    protected $updNotifs;

    /**
     * UserParametersCommandHandler constructor.
     *
     * @param TokenStorage $tokenStorage
     * @param UserManagerInterface $userManager
     * @param UpdateNotificationSettingsHandler $updNotifs
     */
    public function __construct(
        TokenStorage $tokenStorage,
        UserManagerInterface $userManager,
        UpdateNotificationSettingsHandler $updNotifs
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->updNotifs = $updNotifs;
    }

    public function handle(UpdateUserParametersCommand $userParametersCommand)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $user->setNotficationRequete($userParametersCommand->publicationNotification);
        $user->setActivityNewsletterEnabled($userParametersCommand->activityNewsletter);

        if (!is_null($userParametersCommand->newPassword)) {
            $user->setPlainPassword($userParametersCommand->newPassword);
        }

        $this->userManager->updateUser($user);

        foreach ($userParametersCommand->notificationsSettings as $notification) {
            $notification->setScheduleDay($userParametersCommand->scheduleDay);
            $notification->setScheduleHour($userParametersCommand->scheduleHour);
            $this->updNotifs->handle(new UpdateNotificationSettingsCommand($notification));
        }
    }
}
