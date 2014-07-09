<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class ExpBesoinManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheBundle\Entity\ExpBesoin';

    public function countQuestions()
    {
        return $this->getRepository()->countQuestions()->getQuery()->getSingleScalarResult();
    }

}