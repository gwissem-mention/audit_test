<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\AutodiagBundle\Manager\QuestionManager;
use Doctrine\ORM\EntityManager;

/**
 * Manager de l'entité Reponse.
 */
class ReponseManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Reponse';

    /**
     * @var \HopitalNumerique\AutodiagBundle\Manager\ReponseManager
     */
    private $resultatManager;

    /**
     * @var \HopitalNumerique\AutodiagBundle\Manager\ReponseManager
     */
    private $questionManager;

    public function __construct (EntityManager $em, ResultatManager $resultatManager, QuestionManager $questionManager)
    {
        parent::__construct($em);
        $this->resultatManager = $resultatManager;
        $this->questionManager = $questionManager;
    }

    /**
     * Prépare le tableau de réponse, effectue les calculs de moyenne et ajoute les réponses
     *
     * @param array    $resultats Liste des Résultats
     * @param Resultat $synthese  Objet Synthese
     *
     * @return empty
     */
    public function buildNewReponses( $resultats, $synthese )
    {
        $resultats = $this->resultatManager->findBy( array( 'id' => $resultats ) );
        $syntheseReponses = array();

        foreach( $resultats as $resultat ) {
            $reponses = $resultat->getReponses();
            foreach( $reponses as $reponse) {
                //prepare entry
                if( !isset( $syntheseReponses[ $reponse->getQuestion()->getId() ] ) )
                    $syntheseReponses[ $reponse->getQuestion()->getId() ] = array();
                //add entry
                $syntheseReponses[ $reponse->getQuestion()->getId() ][] = $reponse;
            }

            //link Resultat object
            $synthese->addResultat( $resultat );
        }

        $moyennes = array();
        foreach($syntheseReponses as $idQuestion => $reponses ){
            //get entity Question
            $question = $this->questionManager->findOneBy( array('id' => $idQuestion ) );

            $nbVal = 0;
            $val   = 0;
            $exist = false;
            $isNC  = true;

            //calc moyenne
            foreach($reponses as $reponse)
            {
                if ( $reponse->getValue() != -1 && $reponse->getValue() != '' )
                {
                    $val += $reponse->getValue() != '' ? $reponse->getValue() : 0;
                    $nbVal++;
                    $exist = true;
                }

                if($reponse->getValue() != -1)
                {
                    $isNC = false;
                }
            }
            if ($exist)
                $val = $nbVal != 0 ? ( $val / $nbVal ) : -1;
            elseif($isNC)
                $val = -1;
            else
                $val = '';

            //create entity Reponse
            $rep = $this->createEmpty();
            $rep->setQuestion( $question );
            $rep->setResultat( $synthese );
            $rep->setRemarque( '' );
            $rep->setValue( $val );

            $moyennes[] = $rep;
        }

        $this->save( $moyennes );
    }

}