<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Stat.
 */
class StatClicManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheBundle\Entity\StatClic';

    /**
     * Retourne la liste des clicks par réponse
     *
     * @return QueryBuilder
     */
    public function getNbNoteByReponse( $dateDebut, $dateFin )
    {
        // echo '<pre>';
        // \Doctrine\Common\Util\Debug::dump($this->getRepository()->getNbNoteByReponse( $dateDebut, $dateFin )->getQuery()->getSQL());
        // die();

        $statClics = $this->getRepository()->getNbNoteByReponse( $dateDebut, $dateFin )->getQuery()->getResult();

        foreach ($statClics as $key => $statClic) 
        {
            $statClics[$key]["nbClic"] = intval($statClic["nbClic"]);
        }

        return $statClics;
    }
}