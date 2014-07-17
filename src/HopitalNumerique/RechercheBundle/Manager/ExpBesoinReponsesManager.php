<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class ExpBesoinReponsesManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses';

    public function countReponses($expBesoin)
    {
        return $this->getRepository()->countReponses($expBesoin)->getQuery()->getSingleScalarResult();
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param topic $topic      topic concerné
     * @param array $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferences($expBesoinReponses, $references)
    {
        $selectedReferences = $expBesoinReponses->getReferences();

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
     * Formatte les références sous forme d'un unique tableau
     *
     * @param topic $topic      topic concerné
     * @param array $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferencesOwn($topic)
    {
        $return = array();
        $selectedReferences = $topic->getReferences();

        //applique les références 
        foreach( $selectedReferences as $selected ){
            $reference = $selected->getReference();

            //on remet l'élément à sa place
            $return[ $reference->getId() ]['nom']     = $reference->getCode() . " - " . $reference->getLibelle();
            $return[ $reference->getId() ]['primary'] = $selected->getPrimary();
            
            if( $reference->getParent() )
                $return[ $reference->getParent()->getId() ]['childs'][] = $reference->getId();
        }
        
        $this->formatReferencesOwn( $return );
        
        return $return;
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

        foreach($elements as $element) 
        {
            $reponse = $this->findOneBy( array('id' => $element['id']) );
            $reponse->setOrder( $order );
            $order++;
        }
    }

    /**
     * Récupère l'ensemble des réponses dans un tableau avec comme clé l'id de la réponse
     *
     * @return array[ExpBesoinReponses] $expBesoinReponses
     * 
     */
    public function getAllReponsesInArrayById()
    {
        $resultats         = array();
        $expBesoinReponses = $this->findBy(array(), array('order' => 'ASC'));

        foreach ($expBesoinReponses as $expBesoinReponse)
        {
            $resultats[$expBesoinReponse->getId()]                  = array();
            $resultats[$expBesoinReponse->getId()]['libelle']       = $expBesoinReponse->getLibelle();
            $resultats[$expBesoinReponse->getId()]['autreQuestion'] = $expBesoinReponse->isAutreQuestion();
            if(!is_null($expBesoinReponse->getRedirigeQuestion())
                $resultats[$expBesoinReponse->getId()]['idQuestion']    = $expBesoinReponse->getRedirigeQuestion()->getId();
            //$resultats[$expBesoinReponse->getId()]['reference'] = $expBesoinReponse->getReferences();
        }

        return json_encode($resultats);
    }

    /**
     * [formatReferencesOwn description]
     *
     * @param  [type] $retour [description]
     *
     * @return [type]
     */
    private function formatReferencesOwn( &$retour )
    {
        foreach( $retour as $key => $one ){
            $retour[ $key ]['childs'] = $this->getChilds($retour, $one);
        }
    }
    
    /**
     * [getChilds description]
     *
     * @param  [type] $retour [description]
     * @param  [type] $elem   [description]
     *
     * @return [type]
     */
    private function getChilds(&$retour, $elem)
    {
        if( isset( $elem['childs'] ) && count($elem['childs']) ){
            $childs = array();
            foreach( $elem["childs"] as $key => $one ){
                $childs[ $one ] = $retour[ $one ];
                $petitsEnfants  = $this->getChilds($retour, $childs[ $one ]);
                if( $petitsEnfants ){
                    $childs[ $one ]['childs'] = $petitsEnfants;
                    unset( $retour[ $one ] );
                } else {
                    unset( $retour[ $one ] );
                }
            }
            return $childs;
        } else {
            return false;
        }
    }

}