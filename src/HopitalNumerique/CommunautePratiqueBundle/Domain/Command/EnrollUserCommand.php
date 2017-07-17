<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;

/**
 * Enroll user to communaute de pratique action
 */
class EnrollUserCommand
{
    /**
     * @var User $user
     */
    public $user;

    /**
     * EnrollUserCommand constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
