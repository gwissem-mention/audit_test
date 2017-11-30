<?php

namespace HopitalNumerique\RechercheParcoursBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;

/**
 * Class GuidedSearchUpdatedEvent.
 */
class GuidedSearchUpdatedEvent extends Event
{
    /**
     * @var RechercheParcoursGestion $parcoursGestion
     */
    protected $parcoursGestion;

    /**
     * @var string $reason Reason of update
     */
    protected $reason;

    /**
     * GuidedSearchUpdatedEvent constructor.
     *
     * @param RechercheParcoursGestion $parcoursGestion Guided search
     * @param string                   $reason          Update reason
     */
    public function __construct(RechercheParcoursGestion $parcoursGestion, $reason = '')
    {
        $this->parcoursGestion = $parcoursGestion;
        $this->reason = $reason;
    }

    /**
     * @return RechercheParcoursGestion
     */
    public function getParcoursGestion()
    {
        return $this->parcoursGestion;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}
