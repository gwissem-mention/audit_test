<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Entity\Contenu;

/**
 * Class UnsubscribeToObjectCommand.
 */
class UnsubscribeToObjectCommand
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var Objet
     */
    public $object;

    /**
     * @var Contenu
     */
    public $content;

    /**
     * UnsubscribeToObjectCommand constructor.
     *
     * @param User $user
     * @param Objet $object
     * @param Contenu $content
     */
    public function __construct(User $user, Objet $object, Contenu $content = null)
    {
        $this->object = $object;
        $this->user = $user;
        $this->content = $content;
    }
}
