<?php

namespace HopitalNumerique\EtablissementBundle\Manager;

use Doctrine\ORM\EntityManager;
use Nodevo\AdminBundle\Manager\Manager as BaseManager;

class EtablissementManager extends BaseManager
{
    protected $_class = '\HopitalNumerique\EtablissementBundle\Entity\Etablissement';

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