<?php

namespace HopitalNumerique\UserBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class UpdateUserParametersCommandHandler
{
    /** @var TokenStorage $tokenStorage */
    protected $tokenStorage;
    /** @var UserManagerInterface $userManager */
    protected $userManager;

    /**
     * UserParametersCommandHandler constructor.
     *
     * @param TokenStorage $tokenStorage
     * @param UserManagerInterface $userManager
     */
    public function __construct(TokenStorage $tokenStorage, UserManagerInterface $userManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
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
    }
}
