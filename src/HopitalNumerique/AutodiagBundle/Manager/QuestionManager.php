<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use HopitalNumerique\AutodiagBundle\Entity\Question;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Question.
 */
class QuestionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Question';

    /**
     * Compte le nombre de questions lié au chapitre
     *
     * @param Chapitre $chapitre chapitre
     *
     * @return integer
     */
    public function countQuestions( $chapitre )
    {
        return $this->getRepository()->countQuestions($chapitre)->getQuery()->getSingleScalarResult();
    }

    /**
     * Enregistre la question
     *
     * @param  Question $question La question
     *
     * @return empty
     */
    public function saveQuestion( Question $question )
    {
        if( $question->getType()->getId() == 417 ){
            $question->setOptions( null );
            $question->setNoteMinimale( null );
        }else{
            $question->setSeuil( null );
        }

        //cas nouveau
        if( is_null($question->getId()) ){
            $outil = $question->getChapitre()->getOutil();
            $question->setOrder( $this->calcOrder($outil) );
        }

        $this->save( $question );
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param Question $question   La question concernée
     * @param array    $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferences($question, $references)
    {
        $selectedReferences = $question->getReferences();

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

    public function calculPonderationLeft( $question )
    {
        //get Outil
        $outil = $question->getChapitre()->getOutil();

        //init some vars
        $ponderationMax = 100;
        $questions      = array();
        $chapitres      = $outil->getChapitres();
        
        //build big array of questions
        foreach($chapitres as $chapitre)
            $questions = array_merge($questions, $chapitre->getQuestions()->toArray() );
        
        //calcul pondération
        foreach( $questions as $key => $one) {
            if( $one->getId() != $question->getId() ){
                $ponderation = $one->getPonderation();

                if( $ponderation != 0 ){
                    $ponderationMax -= $ponderation;
                    unset( $questions[$key] );
                }    
            }
        }

        //max Pondération invalid
        if( $ponderationMax < 0 )
            return 0;

        return ($question->getPonderation() <= $ponderationMax) ? true : $ponderationMax;
    }









    /**
     * Calcul l'ordre de la question par rapport à l'outil
     *
     * @param Outil $outil L'outil
     *
     * @return integer
     */
    private function calcOrder( $outil )
    {
        return $this->getRepository()->calcOrder($outil)->getQuery()->getSingleScalarResult() + 1;
    }
}