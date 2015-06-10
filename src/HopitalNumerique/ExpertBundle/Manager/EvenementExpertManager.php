<?php

namespace HopitalNumerique\ExpertBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité EvenementExpert.
 */
class EvenementExpertManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ExpertBundle\Entity\EvenementExpert';

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