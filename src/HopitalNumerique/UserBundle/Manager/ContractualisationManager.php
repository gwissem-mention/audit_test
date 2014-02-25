<?php

namespace HopitalNumerique\UserBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Contractualisation.
 */
class ContractualisationManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\UserBundle\Entity\Contractualisation';
    
    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {        
        return $this->getRepository()->getDatasForGrid( $condition );
    }

}