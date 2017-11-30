<?php

namespace HopitalNumerique\ModuleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class HopitalNumeriqueModuleBundle extends Bundle
{
    /**
     * List of module ids that will be concerned by 'COMING_TRAINING_SESSION' event
     *
     * @see \HopitalNumerique\ModuleBundle\Command\ComingTrainingSessionsCommand
     */
    const MODULE_TO_BE_NOTIFIED = [6];
}
