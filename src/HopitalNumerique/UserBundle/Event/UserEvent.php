<?php

namespace HopitalNumerique\UserBundle\Event;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserEvent extends Event
{
    protected $_user;
    protected $_domainesId;

    public function __construct(User $user)
    {
        $this->_user = $user;
        //Evite l'overide post update sur la modif de l'user
        $this->_domainesId = $user->getDomainesId();
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function getDomainesId()
    {
        return $this->_domainesId;
    }
}