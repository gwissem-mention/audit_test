<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Module.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ModuleManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ModuleBundle\Entity\Module';
    
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
        $results = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
        return $this->rearangeForProduction( $results );
    }
    
    
    
    
    
    
    /**
     * Réarrange les objets pour afficher correctement les types
     *
     * @param array $results Les résultats de la requete
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    private function rearangeForProduction( $results )
    {
        $objets  = array();
    
        foreach($results as $result)
        {
            if( isset( $objets[ $result['id'] ] ) )
                $objets[ $result['id'] ]['prod_titre'] .= ', ' . $result['prod_titre'];
            else
                $objets[ $result['id'] ] = $result;
        }
    
        return array_values($objets);
    }
}