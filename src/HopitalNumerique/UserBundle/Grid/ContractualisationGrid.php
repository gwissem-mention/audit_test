<?php

namespace HopitalNumerique\UserBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Contractualisation.
 */
class ContractualisationGrid extends Grid implements IGrid
{
    /**
     * Définie la config spécifique au grid Contractualisation.
     */
    public function setConfig()
    {
        $this->setSource( 'HopitalNumeriqueUserBundle:Contractualisation' );
        $this->setNoDataMessage('Aucun fichier de contractualisation à afficher.');
        $this->setButtonSize(43);
    }

    /**
     * Ajoute les colonnes du grid Contractualisation.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('nomDocument', 'Nom document') );
        
        $colonneDate = new Column\DateColumn('dateRenouvellement', 'Date renouvellement');
        $colonneDate->setSize(170);
        $this->addColonne( $colonneDate );     

        $typeDocumentColonne = new Column\AssocColumn('typeDocument.libelle', 'Type de document');
        $this->addColonne( $typeDocumentColonne );

        $archiverColonne = new Column\BooleanColumn('archiver', 'Document archivé');
        $archiverColonne->setSize(150);
        $this->addColonne( $archiverColonne );

        /* Colonnes inactives */
        $this->addColonne( new Column\BlankColumn('path') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\DownloadButton( 'hopitalnumerique_user_contractualisation_dowload' ) );
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_user_contractualisation_show' ) );
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_user_contractualisation_edit' ) );

        //Custom Archive button : Affiche le bouton archiver
        $archiveButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_user_contractualisation_archiver');
        $archiveButton->setRouteParameters( array('id') );
        $archiveButton->manipulateRender(function($action, $row) {
            if( $row->getField( 'archiver') )
                $action->setAttributes( array('class'=>'btn btn-warning fa fa-archive', 'title' => 'Archiver') );
            else
                $action->setAttributes( array('class'=>'btn btn-warning fa fa-folder-open', 'title' => 'Désarchiver') );

            return $action;
        });
        $this->addActionButton( $archiveButton );
        
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_user_contractualisation_delete' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}