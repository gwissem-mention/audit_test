<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Manager de l'entité Module.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ModuleManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ModuleBundle\Entity\Module';
    protected $_userManager;
        
    /**
     * Constructeur du manager
     *
     * @param EntityManager $em Entity Manager de Doctrine
     */
    public function __construct( EntityManager $em, UserManager $userManager)
    {
        parent::__construct($em);
        $this->_userManager = $userManager;
    }
    
    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $modulesForGrid = array();

        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();

        $modules = $this->getRepository()->getDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();

        foreach ($modules as $module) 
        {
            if(!array_key_exists($module['id'], $modulesForGrid))
            {
                $modulesForGrid[$module['id']] = $module;
            }
            else
            {
                $modulesForGrid[$module['id']]['domaineNom'] .= ";" . $module['domaineNom'];
            }
        }

        return $this->rearangeForProduction( array_values($modulesForGrid) );
    }
    
    public function getAllInscriptionsBySessionsActivesNonPasseesByModules()
    {
        return $this->getRepository()->getAllInscriptionsBySessionsActivesNonPasseesByModules()->getQuery()->getResult();
    }

    
    public function getModuleActifForDomaine($domaineId)
    {
        return $this->getRepository()->getModuleActifForDomaine($domaineId)->getQuery()->getResult();
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