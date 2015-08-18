<?php

namespace Nodevo\TexteDynamiqueBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Manager de l'entité Code.
 */
class CodeManager extends BaseManager
{
    protected $_class = 'Nodevo\TexteDynamiqueBundle\Entity\Code';
    protected $_userManager;
        
    /**
     * Constructeur du manager
     *
     * @param EntityManager $em Entity Manager de Doctrine
     */
    public function __construct( EntityManager $em, UserManager $userManager )
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
        $codesForGrid = array();

        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();

        $codes = $this->getRepository()->getDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();

        foreach ($codes as $code) 
        {
            if(!array_key_exists($code['id'], $codesForGrid))
            {
                $codesForGrid[$code['id']] = $code;
            }
            else
            {
                $codesForGrid[$code['id']]['domaineNom'] .= ";" . $code['domaineNom'];
            }
        }

        return array_values($codesForGrid);
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getCodesByDomaines($code)
    {
        $codesOrdered = array();
        $codes = $this->findBy(array('code' => $code));

        //Parcourts des codes pour les trier par domaine
        foreach ($codes as $code) 
        {
            foreach ($code->getDomaines() as $domaine) 
            {
                if(!array_key_exists($domaine->getId(), $codesOrdered))
                {
                    $codesOrdered[$domaine->getId()] = '';
                }

                $codesOrdered[$domaine->getId()] = $code->getTexte();
            }
        }

        return $codesOrdered;
    }

}