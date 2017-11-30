<?php

namespace HopitalNumerique\UserBundle\Event;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class UserRoleUpdatedEvent.
 */
class UserRoleUpdatedEvent extends Event
{

    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var string $oldRole
     */
    protected $oldRole;

    /**
     * UserRoleUpdatedEvent constructor.
     *
     * @param User   $user
     * @param string $oldRole
     */
    public function __construct(User $user, $oldRole)
    {
        $this->user = $user;
        $this->oldRole = $oldRole;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getOldRole()
    {
        return $this->oldRole;
    }
}
