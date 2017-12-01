<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DTO\News;

use HopitalNumerique\UserBundle\Entity\User;

class NewMemberItem implements WallItemInterface
{
    /**
     * @var User $user
     */
    protected $user;

    /**
     * NewMemberItem constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->user->getCommunautePratiqueEnrollmentDate();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'new_member';
    }

}
