<?php

namespace HopitalNumerique\ObjetBundle\Event;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ObjetEvent
 */
class ObjetEvent extends Event
{
    protected $user;
    protected $objet;
    protected $type;

    /**
     * ObjetEvent constructor.
     *
     * @param Objet     $objet
     * @param User|null $user
     * @param int       $type
     */
    public function __construct(Objet $objet, User $user = null, $type = 1)
    {
        $this->objet = $objet;
        $this->user  = $user;
        $this->type = $type;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Objet
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
