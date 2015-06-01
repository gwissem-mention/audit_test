<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Manager de l'entité ExpBesoinGestion.
 */
class ExpBesoinGestionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheBundle\Entity\ExpBesoinGestion';
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
        $expBesoinGestionsForGrid = array();

        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();

        $expBesoinGestions = $this->getRepository()->getDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();

        foreach ($expBesoinGestions as $expBesoinGestion) 
        {
            if(!array_key_exists($expBesoinGestion['id'], $expBesoinGestionsForGrid))
            {
                $expBesoinGestionsForGrid[$expBesoinGestion['id']] = $expBesoinGestion;
            }
            else
            {
                $expBesoinGestionsForGrid[$expBesoinGestion['id']]['domaineNom'] .= ";" . $expBesoinGestion['domaineNom'];
            }
        }

        return array_values($expBesoinGestionsForGrid);
    }
}