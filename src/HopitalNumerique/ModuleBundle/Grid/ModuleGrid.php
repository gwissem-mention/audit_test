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
        $this->setSource( 'HopitalNumeriqueModuleBundle:Module' );
        $this->setNoDataMessage('Aucun Module à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Module.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('id', 'Id') );
        $this->addColonne( new Column\TextColumn('titre', 'Titre') );
        $this->addColonne( new Column\TextColumn('horairesType', 'Horairestype') );
        $this->addColonne( new Column\TextColumn('lieu', 'Lieu') );
        $this->addColonne( new Column\TextColumn('description', 'Description') );
        $this->addColonne( new Column\TextColumn('nombrePlaceDisponible', 'Nombreplacedisponible') );
        $this->addColonne( new Column\TextColumn('prerequis', 'Prerequis') );
        $this->addColonne( new Column\TextColumn('path', 'Path') );        
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_module_module_show' ) );
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