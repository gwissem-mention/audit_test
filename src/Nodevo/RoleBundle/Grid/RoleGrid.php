<?php 
namespace Nodevo\RoleBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du Grid Role
 */
class RoleGrid extends Grid implements GridInterface
{
    /**
     * Set la config propre au Role Grid (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource( 'NodevoRoleBundle:Role' );
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
        $initialColumn->setSize( 110 );
        $this->addColonne( $initialColumn );

        $etatColonne = new Column\AssocColumn('etat.libelle', 'Etat');
        $etatColonne->setSize( 70 );
        $this->addColonne( $etatColonne );

        /* Colonnes inactives */
        $this->addColonne( new Column\BlankColumn('role') );
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