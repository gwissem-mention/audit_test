<?php

namespace HopitalNumerique\AideBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\AideBundle\Entity\Aide;

/**
 * Manager de l'entité Aide.
 */
class AideManager extends BaseManager
{
    protected $class = 'HopitalNumerique\AideBundle\Entity\Aide';
}
