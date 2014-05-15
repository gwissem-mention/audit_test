<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
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

    /**
     * Met à jour l'ordre des chapitres de manière récursive
     *
     * @param array  $elements Les éléments
     * @param Object $parent   L'élément parent | null
     *
     * @return empty
     */
    public function reorder( $elements, $parent )
    {
        $order = 1;

        foreach($elements as $element) {
            $chapitre = $this->findOneBy( array('id' => $element['id']) );
            $chapitre->setOrder( $order );
            $chapitre->setParent( $parent );
            $order++;

            if( isset($element['children']) )
                $this->reorder( $element['children'], $chapitre );
        }
    }

    /**
     * Retourne les chapitres liés à l'outil sous forme d'arbo
     *
     * @param Outil $outil L'outil
     *
     * @return array
     */
    public function getArbo( $outil )
    {
        $datas = new ArrayCollection( $this->getRepository()->getArbo( $outil )->getQuery()->getResult() );

        //Récupère uniquement les premiers parents
        $criteria = Criteria::create()->where(Criteria::expr()->eq("parent", null) );
        $parents  = $datas->matching( $criteria );
        
        //call recursive function to handle all datas
        return $this->getArboRecursive($datas, $parents, array() );
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param Chapitre $chapitre   Chapitre concerné
     * @param array    $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferences($chapitre, $references)
    {
        $selectedReferences = $chapitre->getReferences();

        //applique les références 
        foreach( $selectedReferences as $selected )
        {
            //on récupère l'élément que l'on va manipuler
            $ref = $references[ $selected->getReference()->getId() ];

            //on le met à jour 
            $ref->selected = true;
            $ref->primary  = $selected->getPrimary();

            //on remet l'élément à sa place
            $references[ $selected->getReference()->getId() ] = $ref;
        }
        
        return $references;
    }



























    /**
     * Fonction récursive qui parcourt l'ensemble des références en ajoutant l'element et recherchant les éventuels enfants
     *
     * @param ArrayCollection $items    Données d'origine
     * @param array           $elements Tableau d'élements à ajouter
     * @param array           $tab      Tableau de données vide à remplir puis retourner
     *
     * @return array
     */
    private function getArboRecursive( ArrayCollection $items, $elements, $tab )
    {        
        foreach($elements as $element)
        {
            //construction de l'element current
            $item             = new \stdClass;
            $item->title      = $element->getTitle();
            $item->id         = $element->getId();
            $item->order      = $element->getOrder();
            $item->references = count($element->getReferences());

            //add childs : filter items with current element
            $criteria     = Criteria::create()->where(Criteria::expr()->eq("parent", $element))->orderBy(array( "order" => Criteria::ASC ));
            $childs       = $items->matching( $criteria );
            $item->childs = $this->getArboRecursive($items, $childs, array() );

            //add current item to big table
            $tab[] = $item;
        }

        //return big table
        return $tab;
    }

}
