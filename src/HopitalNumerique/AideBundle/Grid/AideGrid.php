<?php

namespace HopitalNumerique\AideBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * Configuration du grid Aide.
 */
class AideGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Aide.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_aide.manager.aide' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
    }

    /**
     * Ajoute les colonnes du grid Aide.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('route', 'Route') );

        $libelleColumn = new Column\BooleanColumn('libelle', 'Libellé');
        $libelleColumn->setVisible(false)->setFilterable(false);
        $this->addColonne( $libelleColumn );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton('hopitalnumerique_aide_aide_show') );
        $this->addActionButton( new Action\EditButton('hopitalnumerique_aide_aide_edit') );
        $this->addActionButton( new Action\DeleteButton('hopitalnumerique_aide_aide_delete') );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Supprimer', 'HopitalNumeriqueAideBundle:Aide:deleteMass') );
    }
}