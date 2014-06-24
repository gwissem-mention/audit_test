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
    private $_refPonderees;

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
    public function getArbo( $outil, $refPonderees )
    {
        $this->_refPonderees = $refPonderees;

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
     * Retourne la liste des chapitres et questions pour la vue liste
     *
     * @param Outil $outil L'outil
     *
     * @return array
     */
    public function getChapitresForListe( $outil )
    {
        //build chapitres
        $chapitres      = $outil->getChapitres();
        $parents        = array();
        $enfants        = array();

        foreach($chapitres as $one){
            $chapitre = new \StdClass;
            
            $chapitre->id      = $one->getId();
            $chapitre->title   = $one->getTitle();
            $chapitre->noteMin = $one->getNoteMinimale();
            $chapitre->noteOpt = $one->getNoteOptimale();
            $chapitre->order   = $one->getOrder();
            $chapitre->childs  = array();
            $chapitre->parent  = !is_null($one->getParent()) ? $one->getParent()->getId() : null;

            //handle questions
            $questions         = $one->getQuestions();
            $chapitreQuestions = array();
            foreach ($questions as $question)
                $chapitreQuestions[] = $question;

            $chapitre->questions = $chapitreQuestions;

            //handle héritage
            if( is_null($one->getParent()) ){
                $parents[ $one->getId() ] = $chapitre;
            }else
                $enfants[] = $chapitre;
        }

        //reformate les chapitres
        foreach($enfants as $enfant){
            $parent = $parents[ $enfant->parent ];
            $parent->childs[] = $enfant;
        }

        //make a $sort array for multi-sort function
        $sort = array();
        foreach($parents as $k=>$v) {
            $sort['order'][$k] = $v->order;
            $sort['title'][$k] = $v->title;
            $sort['id'][$k]    = $v->id;
        }
        //sort by order asc, title asc, id desc
        array_multisort($sort['order'], SORT_ASC, $sort['title'], SORT_ASC,$sort['id'], SORT_DESC, $parents);

        return $parents;
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
            $item               = new \stdClass;
            $item->title        = $element->getTitle();
            $item->id           = $element->getId();
            $item->order        = $element->getOrder();
            $item->noteMinimale = $element->getNoteMinimale();
            $item->noteOptimale = $element->getNoteOptimale();
            $item->note         = $this->getNoteReferencement($element->getReferences());

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

    /**
     * Retourne la note des références
     *
     * @param array $references   Tableau des références
     * @param array $ponderations Tableau des pondérations
     *
     * @return integer
     */
    private function getNoteReferencement( $references )
    {
        $note = 0;
        foreach($references as $reference){
            $id = $reference->getReference()->getId();

            if( isset($this->_refPonderees[ $id ]) )
                $note += $this->_refPonderees[ $id ]['poids'];
        }
        
        return $note;
    }
}
