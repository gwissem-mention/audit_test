<?php

namespace Nodevo\FaqBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Manager de l'entité Faq.
 */
class FaqManager extends BaseManager
{
    protected $_class = 'Nodevo\FaqBundle\Entity\Faq';
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
        $faqsForGrid = array();

        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();

        $faqs = $this->getRepository()->getDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();

        foreach ($faqs as $faq) 
        {
            if(!array_key_exists($faq['id'], $faqsForGrid))
            {
                $faqsForGrid[$faq['id']] = $faq;
            }
            else
            {
                $faqsForGrid[$faq['id']]['domaineNom'] .= ";" . $faq['domaineNom'];
            }
        }

        return array_values($faqsForGrid);
    }

    /**
     * Récupération des éléments de FAQ pour le domaine passé en param
     *
     * @param [type] $domaineId [description]
     *
     * @return [type]
     */
    public function getFaqByDomaine($domaineId)
    {
        return $this->getRepository()->getFaqByDomaine( $domaineId )->getQuery()->getResult();
    }
}