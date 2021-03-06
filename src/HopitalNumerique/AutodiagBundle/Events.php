<?php

namespace HopitalNumerique\AutodiagBundle;

/**
 * Contains all events thrown in the autodiag bundle.
 */
final class Events
{
    /**
     * This event occurs when an entry is updated (entry values have changed).
     */
    const ENTRY_UPDATED = 'entry.updated';

    /**
     * This event occurs when a synthesis is generator from others syntheses
     * THe event listerner method receives a HopitalNumerique\AutodiagBundle\Event\SynthesisEvent.
     */
    const SYNTHESIS_GENERATED = 'synthesis.generated';

    /**
     * This event occurs when a Synthesis is unvalidated
     * The event listener method receives a HopitalNumerique\AutodiagBundle\Event\SynthesisEvent.
     */
    const SYNTHESIS_UNVALIDATED = 'synthesis.unvalidated';
    const SYNTHESIS_VALIDATED = 'synthesis.validated';

    const SYNTHESIS_SHARED = 'synthesis.shared';

    /**
     * This event occurs when an autodiag is updated with notification request.
     */
    const AUTODIAG_UPDATE_PUBLISHED = 'autodiag_update_published';
}
