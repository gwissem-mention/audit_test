<?php 
namespace Nodevo\MenuBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du Grid Menu
 */
class MenuGrid extends Grid implements IGrid
{
    /**
     * Set la config propre au Menu Grid (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource( 'NodevoMenuBundle:Menu' );
        $this->setNoDataMessage( 'Aucun élément de menu présent' );
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('name', 'Nom') );
        $this->addColonne( new Column\TextColumn('alias', 'Alias') );
        $this->addColonne( new Column\LockedColumn() );

        /* Colonnes inactives */
        $this->addColonne( new Column\BlankColumn('cssClass') );
        $this->addColonne( new Column\BlankColumn('cssId') );
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton('nodevo_menu_menu_edit') );
        $this->addActionButton( new Action\ShowButton('nodevo_menu_item') );
        $this->addActionButton( new Action\DeleteButton('nodevo_menu_menu_delete') );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        
    }
}