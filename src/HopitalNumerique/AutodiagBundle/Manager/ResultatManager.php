<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Resultat.
 */
class ResultatManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Resultat';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {
        $resultats = $this->findBy( array( $condition->field => $condition->value) );
        $results   = array();

        foreach($resultats as $resultat)
        {
            $datas               = array();
            $datas['id']         = $resultat->getId();
            $datas['taux']       = $resultat->getTauxRemplissage() . '%';
            $datas['lastSave']   = $resultat->getDateLastSave();
            $datas['validation'] = $resultat->getDateValidation();

            if( $user = $resultat->getUser() )
            {
                $datas['user']          = $user->getPrenomNom();
                $datas['etablissement'] = $user->getEtablissementRattachementSante() ? $user->getEtablissementRattachementSante()->getNom() : $user->getAutreStructureRattachementSante();
            }else{
                $datas['user']          = '';
                $datas['etablissement'] = '';
            }

            $results[] = $datas;
        }

        return $results;
    }

    /**
     * Formatte les résultats pour la partie backoffice
     *
     * @param Resultat $resultat L'objet Résultat
     *
     * @return array
     */
    public function formateResultat( Resultat $resultat )
    {
        //build reponses array
        $reponses          = $resultat->getReponses();
        $questionsReponses = array();
        foreach($reponses as $reponse) {
            $rep = new \StdClass;

            $question           = $reponse->getQuestion();
            $rep->value         = $reponse->getValue();
            $rep->remarque      = $reponse->getRemarque();
            $rep->id            = $question->getId();
            $rep->question      = $question->getTexte();
            $rep->ordreResultat = $question->getOrdreResultat();
            $rep->noteMinimale  = $question->getNoteMinimale();
            $rep->synthese      = $question->getSynthese();
            $rep->order         = $question->getOrder();
            $rep->type          = $question->getType()->getId();

            $questionsReponses[ $reponse->getQuestion()->getId() ] = $rep;
        }

        //build chapitres and add previous responses
        $chapitres      = $resultat->getOutil()->getChapitres();
        $parents        = array();
        $enfants        = array();
        foreach($chapitres as $one){
            $chapitre = new \StdClass;
            
            $chapitre->id       = $one->getId();
            $chapitre->synthese = $one->getSynthese();
            $chapitre->title    = $one->getTitle();
            $chapitre->childs   = array();
            $chapitre->parent   = !is_null($one->getParent()) ? $one->getParent()->getId() : null;

            //handle questions/reponses
            $questions         = $one->getQuestions();
            $chapitreQuestions = array();
            foreach ($questions as $question){
                if( isset($questionsReponses[ $question->getId() ]) )
                    $chapitreQuestions[] = $questionsReponses[ $question->getId() ];
            }

            $chapitre->questions = $chapitreQuestions;

            //handle 
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

        return $parents;
    }

    /**
     * Récupère le dernier résultat validé
     *
     * @param Outil  $outil L'outil
     * @param User   $user  L'utilisateur connecté
     *
     * @return Resultat
     */
    public function getLastResultatValided( Outil $outil, User $user )
    {
        return $this->getRepository()->getLastResultatValided($outil, $user)->getQuery()->getOneOrNullResult();
    }
}