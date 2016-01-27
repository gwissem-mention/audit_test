<?php

namespace HopitalNumerique\UserBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Manager de l'entité Contractualisation.
 */
class ConnaissanceAmbassadeurManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\UserBundle\Entity\ConnaissanceAmbassadeur';

    public function getConnaissanceAmbassadersOrderedByDomaine(User $user, $domaineIds)
    {
        $connaissanceAmbassadeursOrdered = array();
        $connaissanceAmbassadeurs = $this->findBy(array('user' => $user, 'domaine' => $domaineIds));
        
        foreach ($connaissanceAmbassadeurs as $connaissanceAmbassadeur)
        {
            $connaissanceAmbassadeursOrdered[$connaissanceAmbassadeur->getDomaine()->getId()] = $connaissanceAmbassadeur; 
        }

        return $connaissanceAmbassadeursOrdered;
    }

    public function findByAmbassadeur($ambassadeur)
    {
        return $this->getRepository()->findByAmbassadeur($ambassadeur);
    }
}