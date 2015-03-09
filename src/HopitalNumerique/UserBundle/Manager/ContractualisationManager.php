<?php

namespace HopitalNumerique\UserBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Contractualisation.
 */
class ContractualisationManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\UserBundle\Entity\Contractualisation';
    
    /**
     * Récupère le nombre de contractualisation à renouveler depuis 45jours
     *
     * @return array
     */
    public function getContractualisationsARenouveler()
    {
        return $this->getRepository()->getContractualisationsARenouveler()->getQuery()->getSingleScalarResult();
    }

    /**
     * Override : Récupère les contractualisations pour un utilisateur donné
     *
     * @return array
     */
    public function getContractualisationForGrid( $condition = null )
    {
        return $this->getRepository()->getContractualisationForGrid( $condition )->getQuery()->getResult();
    }
}