<?php

namespace HopitalNumerique\AutodiagBundle\EventListener;

use HopitalNumerique\AutodiagBundle\Event\SynthesisEvent;
use HopitalNumerique\AutodiagBundle\Events;
use HopitalNumerique\AutodiagBundle\Service\Compare\ComparisonCleaner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CompareListener implements EventSubscriberInterface
{
    /** @var ComparisonCleaner */
    protected $comparisonCleaner;

    /**
     * EntryListener constructor.
     *
     * @param ComparisonCleaner $comparisonCleaner
     */
    public function __construct(ComparisonCleaner $comparisonCleaner)
    {
        $this->comparisonCleaner = $comparisonCleaner;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::SYNTHESIS_UNVALIDATED => 'onSynthesisUnvalidated',
        ];
    }

    public function onSynthesisUnvalidated(SynthesisEvent $event)
    {
        $this->comparisonCleaner->cleanRelatedToSynthesis($event->getSynthesis());
    }
}
