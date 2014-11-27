<?php

namespace HopitalNumerique\AutodiagBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Outil.
 */
class OutilGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Outil.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_autodiag.manager.outil' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun outil à afficher.');
        $this->showIDColumn( true );
        $this->setFilterIdColumn( true );
    }

    /**
     * Ajoute les colonnes du grid Outil.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('title', 'Titre') );
        
        $nbFormColumn = new Column\TextColumn('nbForm', 'Nb formulaires en cours');
        $nbFormColumn->setSize( 160 );
        $this->addColonne( $nbFormColumn );

        $nbFormValidColumn = new Column\TextColumn('nbFormValid', 'Nb formulaires finalisés');
        $nbFormValidColumn->setSize( 160 );
        $this->addColonne( $nbFormValidColumn );

        $nbChapColumn = new Column\TextColumn('nbChap', 'Nb chapitres');
        $nbChapColumn->setSize( 110 );
        $this->addColonne( $nbChapColumn );

        $nbQuestColumn = new Column\TextColumn('nbQuest', 'Nb questions');
        $nbQuestColumn->setSize( 110 );
        $this->addColonne( $nbQuestColumn );

        $this->addColonne( new Column\DateColumn('dateCreation', 'Date de création') );

        $statutColonne = new Column\TextColumn('statut', 'Statut');
        $statutColonne->setSize( 80 );
        $statutColonne->setFilterType('select');
        $statutColonne->setSelectFrom('source');
        $statutColonne->setOperatorsVisible( false );
        $statutColonne->setDefaultOperator( \APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ );
        $this->addColonne( $statutColonne );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_autodiag_outil_edit' ) );
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_autodiag_outil_delete' ) );

        $btn = new \APY\DataGridBundle\Grid\Action\RowAction( '', 'hopitalnumerique_autodiag_resultat' );
        $btn->setRouteParameters( array('id') );
        $btn->setAttributes( array('class'=>'btn btn-success fa fa-list','title' => 'Afficher les résultats') );

        $this->addActionButton( $btn );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueAutodiagBundle:Outil:deleteMass') );
        $this->addMassAction( new Action\ActionMass('Activer','HopitalNumeriqueAutodiagBundle:Outil:activerMass') );
        $this->addMassAction( new Action\ActionMass('Désactiver','HopitalNumeriqueAutodiagBundle:Outil:desactiverMass') );
        $this->addMassAction( new Action\ActionMass('Exporter les réponses','HopitalNumeriqueAutodiagBundle:Outil:exportMass') );
    }
}
