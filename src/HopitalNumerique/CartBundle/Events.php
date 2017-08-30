<?php

namespace HopitalNumerique\CartBundle;

/**
 * Contains all events thrown in the cart bundle.
 */
final class Events
{
    /**
     * This event occurs when a report is updated.
     */
    const REPORT_UPDATED = 'report_updated';

    /**
     * This event occurs when a report is shared.
     */
    const REPORT_SHARED = 'report_shared';

    /**
     * This event occurs when a report is copied.
     */
    const REPORT_COPIED = 'report_copied';
}
