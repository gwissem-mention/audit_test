<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class AddObjectUpdateCommand.
 */
class AddObjectUpdateCommand
{
    /**
     * @var Objet
     */
    public $object;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $reason;

    /**
     * AddObjectUpdateCommand constructor.
     *
     * @param Objet $object
     * @param User  $user
     * @param       $reason
     */
    public function __construct(Objet $object, User $user, $reason)
    {
        $this->object = $object;
        $this->user = $user;
        $this->reason = $reason;
    }
}
