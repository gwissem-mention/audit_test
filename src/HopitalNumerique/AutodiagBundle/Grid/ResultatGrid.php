<?php

namespace HopitalNumerique\AutodiagBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Resultat.
 */
class ResultatGrid extends Grid implements IGrid
{
    /**
     * Définie la config spécifique au grid Resultat.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_autodiag.manager.resultat' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun résultat à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Resultat.
     */
    public function setColumns()
    {
        $userColumn = new Column\TextColumn('user', 'Prénom et Nom de l\'utilisateur');
        $userColumn->setFilterable( false );
        $this->addColonne( $userColumn );

        $etablissementColumn = new Column\TextColumn('etablissement', 'Etablissement de rattachement');
        $etablissementColumn->setFilterable( false );
        $this->addColonne( $etablissementColumn );

        $tauxColumn = new Column\TextColumn('taux', 'Taux de remplissage global');
        $tauxColumn->setFilterable( false );
        $tauxColumn->setSize( 180 );
        $this->addColonne( $tauxColumn );

        $lastSaveColumn = new Column\DateColumn('lastSave', 'Date du dernier enregistrement');
        $lastSaveColumn->setFilterable( false );
        $lastSaveColumn->setSize( 200 );
        $this->addColonne( $lastSaveColumn );

        $validationColumn = new Column\DateColumn('validation', 'Date de validation');
        $validationColumn->setFilterable( false );
        $validationColumn->setSize( 130 );
        $this->addColonne( $validationColumn );

        //Colonnes invisible
        $this->addColonne( new Column\BlankColumn('id') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_autodiag_resultat_detail' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {

    }
}