<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Session.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class SessionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ModuleBundle\Entity\Session';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( $condition = null )
    {
        return $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
    }

}