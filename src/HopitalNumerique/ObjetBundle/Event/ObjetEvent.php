<?php

namespace HopitalNumerique\ObjetBundle\Event;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\EventDispatcher\Event;

class ObjetEvent extends Event
{
    protected $_user;
    protected $_objet;
    protected $_type;

    public function __construct(Objet $objet, User $user = null, $type = 1)
    {
        $this->_objet = $objet;
        $this->_user = $user;
        $this->_type = $type;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function getObjet()
    {
        return $this->_objet;
    }

    public function getType()
    {
        return $this->_type;
    }
}
