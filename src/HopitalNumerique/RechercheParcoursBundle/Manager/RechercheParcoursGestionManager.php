<?php

namespace HopitalNumerique\RechercheParcoursBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Manager de l'entité RechercheParcoursGestion.
 */
class RechercheParcoursGestionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion';
    protected $_userManager;

    /**
     * Constructeur du manager gérant les références
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @return void
     */
    public function __construct(EntityManager $entityManager, UserManager $userManager)
    {
        parent::__construct($entityManager);

        $this->_userManager = $userManager;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $rechercheParcoursGestionsForGrid = array();

        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();

        $rechercheParcoursGestions = $this->getRepository()->getDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();

        foreach ($rechercheParcoursGestions as $rechercheParcoursGestion) 
        {
            if(!array_key_exists($rechercheParcoursGestion['id'], $rechercheParcoursGestionsForGrid))
            {
                $rechercheParcoursGestionsForGrid[$rechercheParcoursGestion['id']] = $rechercheParcoursGestion;
            }
            else
            {
                $rechercheParcoursGestionsForGrid[$rechercheParcoursGestion['id']]['domaineNom'] .= ";" . $rechercheParcoursGestion['domaineNom'];
            }
        }

        return array_values($rechercheParcoursGestionsForGrid);
    }
}