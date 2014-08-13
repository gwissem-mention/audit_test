<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Nodevo\ToolsBundle\Tools\Chaine;

/**
 * Manager de l'entité Outil.
 */
class OutilManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Outil';

    /**
     * Sauvegarde l'outil :gère les mises en forme et exceptions
     *
     * @param Outil $outil L'outil à sauvegarder
     *
     * @return empty
     */
    public function saveOutil( Outil $outil )
    {
        //manage alias
        $tool = new Chaine( ( $outil->getAlias() == '' ? $outil->getTitle() : $outil->getAlias() ) );
        $outil->setAlias( $tool->minifie() );

        //Hnadle boolean fields
        if( !$outil->isColumnChart() ){
            $outil->setColumnChartLabel( null );
            $outil->setColumnChartAxe( null );
        }

        if( !$outil->isRadarChart() ){
            $outil->setRadarChartLabel( null );
            $outil->setRadarChartAxe( null );
        }

        $this->save( $outil );
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $outils  = $this->findAllOrdered('title','asc');
        $results = array();

        foreach($outils as $outil) {
            $object                 = array();
            $object['id']           = $outil->getId();
            $object['title']        = $outil->getTitle();
            $object['dateCreation'] = $outil->getDateCreation();
            $object['statut']       = $outil->getStatut()->getLibelle();
            $object['nbChap']       = 0;
            $object['nbQuest']      = 0;
            $object['nbForm']       = 0;
            $object['nbFormValid']  = 0;

            //do some maths
            $chapitres = $outil->getChapitres();
            $object['nbChap'] = count($chapitres);
            foreach($chapitres as $chapitre)
                $object['nbQuest'] += count($chapitre->getQuestions());

            $resultats = $outil->getResultats();
            foreach($resultats as $resultat) {
                if( is_null($resultat->getDateValidation()) )
                    $object['nbForm']++;
                else
                    $object['nbFormValid']++;
            }

            //set result to big array
            $results[] = $object;
        }

        return $results;
    }

    /**
     * Modifie l'état de l'outil
     *
     * @param array     $outils Liste des outils
     * @param Reference $ref    Référence désirée
     *
     * @return empty
     */
    public function toogleState($outils, $ref)
    {
        foreach($outils as $outil)
            $outil->setStatut( $ref );

        //save
        $this->_em->flush();
    }
}