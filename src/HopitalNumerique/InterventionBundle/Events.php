<?php

namespace HopitalNumerique\InterventionBundle;

/**
 * Contain all events throwns in the autodiag bundle
 *
 * @package HopitalNumerique\AutodiagBundle
 */
final class Events
{
    const INTERVENTION_REQUEST = 'intervention.request';
    const INTERVENTION_ACCEPT = 'intervention.accept';
    const INTERVENTION_EVALUATION = 'intervention.evaluation';
    const INTERVENTION_EVALUATION_FRONT = 'intervention.evaluation.front';
}