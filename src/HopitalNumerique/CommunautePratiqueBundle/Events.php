<?php

namespace HopitalNumerique\CommunautePratiqueBundle;

/**
 * Contain all events throwns in the autodiag bundle.
 */
final class Events
{
    /**
     * This event occurs when a user join the Communaute de pratique
     */
    const ENROLL_USER = 'communautepratique.enroll_user';

    /**
     * This event occurs when a user quit the Communaute de pratique
     */
    const DISENROLL_USER = 'communautepratique.disenroll_user';
}
