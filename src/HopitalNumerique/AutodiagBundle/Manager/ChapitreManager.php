<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Chapitre.
 */
class ChapitreManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Chapitre';

    /**
     * Compte le nombre de chapitres lié à loutil
     *
     * @param Outil $outil Outil
     *
     * @return integer
     */
    public function countChapitres( $outil )
    {
        return $this->getRepository()->countChapitres($outil)->getQuery()->getSingleScalarResult();
    }
}