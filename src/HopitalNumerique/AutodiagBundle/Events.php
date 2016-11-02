<?php

namespace HopitalNumerique\AutodiagBundle;

/**
 * Contain all events throwns in the autodiag bundle
 *
 * @package HopitalNumerique\AutodiagBundle
 */
final class Events
{
    /**
     * This event occurs when an entry is updated (entry values have changed)
     */
    const ENTRY_UPDATED = 'entry.updated';

    /**
     * This event occurs when a Synthesis is unvalidated
     * The event listener method receives a HopitalNumerique\AutodiagBundle\Event\SynthesisEvent
     */
    const SYNTHESIS_UNVALIDATED = 'synthesis.unvalidated';
}
