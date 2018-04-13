<?php

namespace HopitalNumerique\CoreBundle\Event;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserSubscribeEvent extends Event
{
    /**
     * @var mixed
     */
    protected $object;

    /**
     * @var User
     */
    protected $user;

    /**
     * UserSubscribeEvent constructor.
     *
     * @param mixed $object
     * @param User $user
     */
    public function __construct($object, User $user)
    {
        $this->object = $object;
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
