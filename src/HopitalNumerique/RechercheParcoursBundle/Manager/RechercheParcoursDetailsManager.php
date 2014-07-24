<?php

namespace HopitalNumerique\RechercheParcoursBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class RechercheParcoursDetailsManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails';

    public function countDetails()
    {
        return $this->getRepository()->countDetails()->getQuery()->getSingleScalarResult();
    }
}