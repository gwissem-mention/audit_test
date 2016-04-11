<?php

namespace HopitalNumerique\GlossaireBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

use HopitalNumerique\GlossaireBundle\Entity\Glossaire;

/**
 * Manager de l'entité Glossaire.
 */
class GlossaireManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\GlossaireBundle\Entity\Glossaire';
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
        $glossairesForGrid = array();

        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();

        $glossaires = $this->getRepository()->getDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();

        foreach ($glossaires as $glossaire) 
        {
            if(!array_key_exists($glossaire['id'], $glossairesForGrid))
            {
                $glossairesForGrid[$glossaire['id']] = $glossaire;
            }
            else
            {
                $glossairesForGrid[$glossaire['id']]['domaineNom'] .= ";" . $glossaire['domaineNom'];
            }
        }

        return array_values($glossairesForGrid);
    }
}