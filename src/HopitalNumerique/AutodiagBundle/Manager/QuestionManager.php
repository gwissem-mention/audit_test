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
     * Retourne la liste des questions dans le bon ordre
     *
     * @param Chapitre $chapitre chapitre
     *
     * @return integer
     */
    public function getQuestionsOrdered( $chapitre, $refPonderees )
    {
        $questions = $this->getRepository()->getQuestionsOrdered($chapitre)->getQuery()->getResult();

        $datas = array();
        foreach($questions as $one) {
            $question              = new \StdClass;
            $question->id          = $one->getId();
            $question->texte       = $one->getTexte();
            $question->ponderation = $one->getPonderation();
            $question->note        = $this->getNoteReferencement( $one->getReferences(), $refPonderees );

            $datas[] = $question;
        }

        return $datas;
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
            $chapitre = $question->getChapitre();
            $question->setOrder( $this->calcOrder($chapitre) );
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

    /**
     * [calculPonderationLeft description]
     *
     * @param  [type] $question [description]
     *
     * @return [type]
     */
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
     * Met à jour l'ordre des questions
     *
     * @param array $elements Les éléments
     *
     * @return empty
     */
    public function reorder( $elements )
    {
        $order = 1;

        foreach($elements as $element) {
            $question = $this->findOneBy( array('id' => $element['id']) );
            $question->setOrder( $order );
            $order++;
        }
    }







    /**
     * Retourne la note des références
     *
     * @param array $references   Tableau des références
     * @param array $ponderations Tableau des pondérations
     *
     * @return integer
     */
    private function getNoteReferencement( $references, $refPonderees )
    {
        $note = 0;
        foreach($references as $reference){
            $id = $reference->getReference()->getId();

            if( isset($refPonderees[ $id ]) )
                $note += $refPonderees[ $id ]['poids'];
        }
        
        return $note;
    }

    /**
     * Calcul l'ordre de la question par rapport au chapitre
     *
     * @param Chapitre $chapitre Le chapitre
     *
     * @return integer
     */
    private function calcOrder( $chapitre )
    {
        return $this->countQuestions( $chapitre ) + 1;
    }
}