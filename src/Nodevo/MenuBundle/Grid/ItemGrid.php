<?php 
namespace Nodevo\MenuBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du Grid Menu
 */
class ItemGrid extends Grid implements IGrid
{
    /**
     * Set la config propre au Menu Grid (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource( 'nodevo_menu.manager.item' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage( 'Aucun élément dans ce menu' );
        $this->setAffichageRecursif( 'idItemParent', 'name' );
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('name', 'Nom') );
        $this->addColonne( new Column\OrderColumn() );

        $displayColumn = new Column\BooleanColumn('display', 'Affiché');
        $displayColumn->setValues( array( 1 => 'Affiché', 0 => 'Non affiché') );
        $displayColumn->setSize(80);
        $this->addColonne( $displayColumn );

        /* Colonnes inactives */
        $this->addColonne( new Column\BlankColumn('idItemParent') );
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton('nodevo_menu_item_edit') );
        $this->addActionButton( new Action\DeleteButton('nodevo_menu_item_delete') );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        
    }
}