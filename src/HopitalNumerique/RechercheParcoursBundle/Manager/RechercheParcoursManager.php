<?php

namespace HopitalNumerique\RechercheParcoursBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;

/**
 * Manager de l'entité Requete.
 */
class RechercheParcoursManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours';

    /**
     * Met à jour l'ordre de la recherche par parcours
     *
     * @param array  $elements Les éléments
     * @param Object $parent   L'élément parent | null
     *
     * @return empty
     */
    public function reorder( $elements )
    {
        $order = 1;

        foreach($elements as $element) 
        {
            $rechercheParcours = $this->findOneBy( array('id' => $element['id']) );
            $rechercheParcours->setOrder( $order );
            $order++;
        }
    }

    /**
     * Récupération de toutes les instances de rechercheParcours fils de rechercheParcoursGestion passé en param ordonnée par leur id
     *
     * @param RechercheParcoursGestion $rechercheParcoursGestion [description]
     *
     * @return [type]
     */
    public function getRechercheParcoursFils(RechercheParcoursGestion $rechercheParcoursGestion)
    {
        $results = array();

        $rechercheParcours = $this->findBy(array('recherchesParcoursGestion' => $rechercheParcoursGestion));

        foreach ($rechercheParcours as $recherche) 
        {
            $results[$recherche->getReference()->getId()] = $recherche;
        }

        return $results;
    }
}