<?php

namespace HopitalNumerique\AutodiagBundle\EventListener;

use HopitalNumerique\AutodiagBundle\Event\SynthesisGeneratedEvent;
use HopitalNumerique\AutodiagBundle\Service\Score\BoundaryCalculator;

class BoundaryScoreListener
{
    /** @var BoundaryCalculator */
    protected $boundaryCalculator;

    /**
     * BoundaryScoreListener constructor.
     *
     * @param BoundaryCalculator $boundaryCalculator
     */
    public function __construct(BoundaryCalculator $boundaryCalculator)
    {
        $this->boundaryCalculator = $boundaryCalculator;
    }

    public function onSynthesisGenerated(SynthesisGeneratedEvent $event)
    {
        $this->boundaryCalculator->guessBoundaries(
            $event->getSynthesis(),
            $event->getSource()
        );
    }
}
