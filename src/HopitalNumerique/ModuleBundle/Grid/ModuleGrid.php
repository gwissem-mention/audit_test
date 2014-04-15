<?php

namespace HopitalNumerique\ModuleBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Module.
 */
class ModuleGrid extends Grid implements IGrid
{
    /**
     * Définie la config spécifique au grid Module.
     */
    public function setConfig()
    {
        //Manager
        $this->setSource( 'hopitalnumerique_module.manager.module' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        
        $this->setNoDataMessage('Aucun module à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Module.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('titre', 'Titre') );
        
        $productionColumn = new Column\TextColumn('prod_titre', 'Productions concernées');
        $productionColumn->setSize( 400 );
        $productionColumn->setFilterType('select');
        $productionColumn->setSelectFrom('source');
        $productionColumn->setOperatorsVisible( false );
        $this->addColonne($productionColumn);
        
        $statutColumn = new Column\TextColumn('statut', 'Statut');
        $statutColumn->setSize( 80 );
        $statutColumn->setFilterType('select');
        $statutColumn->setSelectFrom('source');
        $statutColumn->setOperatorsVisible( false );
        $this->addColonne($statutColumn);
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_module_module_show' ) );
        $this->addActionButton( new Action\FilsButton('hopitalnumerique_module_module_session') );
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_module_module_edit' ) );
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_module_module_edit' ) );
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_module_module_delete' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
    }
}