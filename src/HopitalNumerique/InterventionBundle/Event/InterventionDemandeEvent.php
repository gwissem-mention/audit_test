<?php

namespace HopitalNumerique\AutodiagBundle\Event;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use Symfony\Component\EventDispatcher\Event;

class InterventionEvent extends Event
{
    /**
     * @var InterventionDemande
     */
    protected $intervention;

    public function __construct(InterventionDemande $intervention)
    {
        $this->intervention = $intervention;
    }

    /**
     * @return InterventionDemande
     */
    public function getSynthesis()
    {
        return $this->intervention;
    }
}
