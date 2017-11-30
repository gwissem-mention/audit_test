<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class GroupEvent.
 */
class GroupEvent extends Event
{

    /**
     * @var Groupe $group
     */
    protected $group;

    /**
     * GroupEvent constructor.
     *
     * @param Groupe $group
     */
    public function __construct(Groupe $group)
    {
        $this->group = $group;
    }

    /**
     * @return Groupe
     */
    public function getGroup()
    {
        return $this->group;
    }

}
