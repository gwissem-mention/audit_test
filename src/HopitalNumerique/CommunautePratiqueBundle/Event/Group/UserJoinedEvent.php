<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Group;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Inscription;
use HopitalNumerique\CommunautePratiqueBundle\Event\GroupEvent;

/**
 * Class UserJoinedEvent.
 */
class UserJoinedEvent extends GroupEvent
{

    /**
     * @var Inscription $registration
     */
    protected $registration;

    /**
     * UserJoinedEvent constructor.
     *
     * @param Groupe      $group
     * @param Inscription $registration
     */
    public function __construct(Groupe $group, Inscription $registration)
    {
        parent::__construct($group);
        $this->registration = $registration;
    }

    /**
     * @return Inscription
     */
    public function getRegistration()
    {
        return $this->registration;
    }
}
