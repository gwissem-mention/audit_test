<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Session.
 */
class SessionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ModuleBundle\Entity\Session';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {
        return $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
    }

}