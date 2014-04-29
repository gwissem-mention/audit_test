<?php

namespace HopitalNumerique\AutodiagBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Outil.
 */
class OutilGrid extends Grid implements IGrid
{
    /**
     * Définie la config spécifique au grid Outil.
     */
    public function setConfig()
    {
        $this->setSource( 'HopitalNumeriqueAutodiagBundle:Outil' );
        $this->setNoDataMessage('Aucun Outil à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Outil.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('title', 'Title') );
        $this->addColonne( new Column\TextColumn('alias', 'Alias') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_autodiag_outil_edit' ) );
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_autodiag_outil_delete' ) );

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}