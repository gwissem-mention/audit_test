<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Categorie.
 */
class CategorieManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Categorie';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        return $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
    }
}