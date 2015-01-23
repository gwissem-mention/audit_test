<?php

namespace HopitalNumerique\AutodiagBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Categorie.
 */
class CategorieGrid extends Grid implements GridInterface
{
    /**
     * DÃ©finie la config spÃ©cifique au grid Categorie.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_autodiag.manager.categorie' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucune categorie Ã  afficher.');
    }

    /**
     * Ajoute les colonnes du grid Categorie.
     */
    public function setColumns()
    {
        $titleColumn = new Column\TextColumn('title', 'Titre');
        $titleColumn->setFilterable( false );
        $this->addColonne( $titleColumn );

        $noteColumn = new Column\TextColumn('note', 'Note Optimal');
        $noteColumn->setFilterable( false );
        $this->addColonne( $noteColumn );

        //Colonnes invisible
        $idOutilColumn = new Column\TextColumn('idOutil', 'ID to hide');
        $idOutilColumn->setFilterable( false );
        $idOutilColumn->setVisible( false );
        $this->addColonne( $idOutilColumn );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $editButton = new Action\EditButton( 'hopitalnumerique_autodiag_categorie_edit' );
        $editButton->setRouteParameters( array('idOutil', 'id') );
        $this->addActionButton( $editButton );
        
        // $deleteButton = new Action\DeleteButton( 'hopitalnumerique_autodiag_categorie_delete' );
        // $deleteButton->setRouteParameters( array('idCat') );
        // $deleteButton->setRouteParametersMapping( array( 'idCat' => 'id') );
        // $this->addActionButton( $deleteButton );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueAutodiagBundle:Categorie:deleteMass') );
    }
}