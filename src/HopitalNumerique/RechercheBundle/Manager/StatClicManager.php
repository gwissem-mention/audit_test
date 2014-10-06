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
        $statClics = $this->getRepository()->getNbNoteByReponse( $dateDebut, $dateFin )->getQuery()->getResult();

        $results = array();
        foreach ($statClics as $key => $statClic) 
        {
            if(!array_key_exists($statClic["questionOrder"], $results))
            {
                $results[$statClic["questionOrder"]] = array(
                    'libelle'  => $statClic["questionLibelle"],
                    'statClic' => array()
                );
            }

            $statClic["nbClic"] = intval($statClic["nbClic"]);
            $results[$statClic["questionOrder"]]['statClic'][] = $statClic;
        }

        sort($results);

        return $results;
    }
}