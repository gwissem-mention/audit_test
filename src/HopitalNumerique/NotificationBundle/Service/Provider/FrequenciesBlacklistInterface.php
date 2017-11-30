<?php

namespace HopitalNumerique\NotificationBundle\Service\Provider;

/**
 * Allow providers to given a blacklist of frequencies.
 */
interface FrequenciesBlacklistInterface
{
    /**
     * Gets frequencies blacklist.
     *
     * @return array
     */
    public function getForbiddenFrequencies();
}
