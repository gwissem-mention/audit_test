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
        $this->setSource( 'hopitalnumerique_autodiag.manager.outil' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun Outil à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Outil.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('title', 'Title') );
        
        $nbChapColumn = new Column\TextColumn('nbChap', 'Nb Chapitres');
        $nbChapColumn->setSize( 110 );
        $this->addColonne( $nbChapColumn );

        $nbQuestColumn = new Column\TextColumn('nbQuest', 'Nb Questions');
        $nbQuestColumn->setSize( 110 );
        $this->addColonne( $nbQuestColumn );

        $this->addColonne( new Column\DateColumn('dateCreation', 'Date de création') );

        $statutColonne = new Column\TextColumn('statut', 'Statut');
        $statutColonne->setSize( 80 );
        $statutColonne->setFilterType('select');
        $statutColonne->setSelectFrom('source');
        $statutColonne->setOperatorsVisible( false );
        $this->addColonne( $statutColonne );
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
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueAutodiagBundle:Outil:deleteMass') );
        $this->addMassAction( new Action\ActionMass('Activer','HopitalNumeriqueAutodiagBundle:Outil:activerMass') );
        $this->addMassAction( new Action\ActionMass('Désactiver','HopitalNumeriqueAutodiagBundle:Outil:desactiverMass') );
    }
}