<?php

namespace HopitalNumerique\AutodiagBundle\Event;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Symfony\Component\EventDispatcher\Event;

class SynthesisEvent extends Event
{
    /**
     * @var Synthesis
     */
    protected $synthesis;

    public function __construct(Synthesis $synthesis)
    {
        $this->synthesis = $synthesis;
    }

    /**
     * @return Synthesis
     */
    public function getSynthesis()
    {
        return $this->synthesis;
    }
}
