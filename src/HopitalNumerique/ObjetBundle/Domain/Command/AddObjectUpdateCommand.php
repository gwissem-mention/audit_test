<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use HopitalNumerique\ObjetBundle\Entity\Contenu;
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
     * @var Contenu
     */
    public $contenu;

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
     * @param Contenu $contenu
     * @param User  $user
     * @param       $reason
     */
    public function __construct(Objet $object, User $user, $reason, Contenu $contenu = null)
    {
        $this->object = $object;
        $this->contenu = $contenu;
        $this->user = $user;
        $this->reason = $reason;
    }
}
