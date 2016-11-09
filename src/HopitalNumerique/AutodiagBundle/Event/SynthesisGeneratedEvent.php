<?php

namespace HopitalNumerique\AutodiagBundle\Event;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Symfony\Component\EventDispatcher\Event;

class SynthesisGeneratedEvent extends SynthesisEvent
{
    /**
     * @var Synthesis[]
     */
    protected $source;

    public function __construct(Synthesis $synthesis, $source)
    {
        parent::__construct($synthesis);
        $this->source = $source;
    }

    /**
     * @return Synthesis[]
     */
    public function getSource()
    {
        return $this->source;
    }
}
