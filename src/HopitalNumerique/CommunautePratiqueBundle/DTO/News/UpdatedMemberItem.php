<?php

namespace HopitalNumerique\CommunautePratiqueBundle\DTO\News;

use HopitalNumerique\UserBundle\Entity\User;

class UpdatedMemberItem implements WallItemInterface
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
        return $this->user->getDateLastUpdate();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'updated_member';
    }
}
