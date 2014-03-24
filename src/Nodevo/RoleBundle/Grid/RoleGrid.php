<?php 
namespace Nodevo\RoleBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du Grid Role
 */
class RoleGrid extends Grid implements IGrid
{
    /**
     * Set la config propre au Role Grid (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource( 'nodevo_role.manager.role' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setDefaultLimit( 10 );
        $this->setLimits( array(5, 10, 15) );
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('name', 'Nom') );

        $initialColumn = new Column\BooleanColumn('initial', 'Groupe Initial');
        $initialColumn->setValues( array( 1 => 'Oui', 0 => 'Non') );
        $initialColumn->setSize( 150 );
        $this->addColonne( $initialColumn );

        $etatColonne = new Column\TextColumn('etat', 'Etat');
        $etatColonne->setSize( 80 );
        $this->addColonne( $etatColonne );
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton('nodevo_role_edit') );
        $this->addActionButton( new Action\ShowButton('nodevo_role_show') );
        $this->addActionButton( new Action\DeleteButton('nodevo_role_delete') );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        
    }
}