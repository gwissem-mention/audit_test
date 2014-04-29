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
}