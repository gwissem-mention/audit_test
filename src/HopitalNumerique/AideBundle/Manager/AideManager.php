<?php

namespace HopitalNumerique\AideBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\AideBundle\Entity\Aide;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Manager de l'entité Aide.
 */
class AideManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AideBundle\Entity\Aide';
}