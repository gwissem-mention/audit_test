<?php

namespace HopitalNumerique\InterventionBundle\Event;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use Symfony\Component\EventDispatcher\Event;

class InterventionDemandeEvent extends Event
{
    /**
     * @var InterventionDemande
     */
    protected $intervention;
    protected $oldIntervention;

    public function __construct(InterventionDemande $intervention, InterventionDemande $oldIntervention = null)
    {
        $this->intervention = $intervention;
        $this->oldIntervention = $oldIntervention;
    }

    /**
     * @return InterventionDemande
     */
    public function getInterventionDemande()
    {
        return $this->intervention;
    }

    public function getOldInterventionDemande()
    {
        return $this->oldIntervention;
    }
}
