<?php 
namespace Nodevo\MenuBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du Grid Menu
 */
class MenuGrid extends Grid implements GridInterface
{
    /**
     * Set la config propre au Menu Grid (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource( 'nodevo_menu.manager.menu' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->showIDColumn( false );
        $this->setFilterIdColumn( false );
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
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton('nodevo_menu_menu_edit') );
        $this->addActionButton( new Action\FilsButton('nodevo_menu_item') );
        $this->addActionButton( new Action\DeleteButton('nodevo_menu_menu_delete') );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        
    }
}