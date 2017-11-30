<?php

namespace HopitalNumerique\ModuleBundle;

/**
 * Contain all events thrown in the autodiag bundle.
 */
final class Events
{
    /**
     * This event is fired by the scheduler to notify about coming sessions.
     */
    const COMING_TRAINING_SESSION = 'coming_training_sessions';
}
