<?php

namespace HopitalNumerique\AutodiagBundle\EventListener;

use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value;
use HopitalNumerique\AutodiagBundle\Event\EntryUpdatedEvent;
use HopitalNumerique\AutodiagBundle\Repository\Autodiag\ContainerRepository;
use HopitalNumerique\AutodiagBundle\Service\Score\ScoreCalculator;

class EntryListener
{
    /** @var ContainerRepository */
    protected $containerRepository;

    protected $calculator;

    /**
     * EntryListener constructor.
     *
     * @param ContainerRepository $containerRepository
     * @param ScoreCalculator     $calculator
     */
    public function __construct(ContainerRepository $containerRepository, ScoreCalculator $calculator)
    {
        $this->containerRepository = $containerRepository;
        $this->calculator = $calculator;
    }

    public function onEntryUpdated(EntryUpdatedEvent $event)
    {
        $values = $event->getValues();
        $containers = $this->containerRepository->getConcernedByAttributes(
            array_map(function (Value $value) {
                return $value->getAttribute();
            }, $values)
        );

        $this->calculator->computeEntryScoreForContainers($event->getEntry(), $containers);
    }
}
