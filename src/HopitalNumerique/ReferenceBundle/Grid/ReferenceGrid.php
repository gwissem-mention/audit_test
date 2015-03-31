<?php

namespace HopitalNumerique\ReferenceBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * Configuration du grid Reference.
 */
class ReferenceGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Reference.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_reference.manager.reference' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setAffichageRecursif( 'idParent', 'libelle' );
        $this->showIDColumn( true );
        $this->setFilterIdColumn( false );
    }

    /**
     * Ajoute les colonnes du grid Reference.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('code', 'Code') );
        $this->addColonne( new Column\TextColumn('libelle', 'Libellé') );

        $etatColonne = new Column\TextColumn('etat', 'Etat');
        $etatColonne->setSize( 80 );
        $etatColonne->setFilterType('select');
        $etatColonne->setSelectFrom('source');
        $etatColonne->setOperatorsVisible( false );
        $etatColonne->setDefaultOperator( \APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ );
        $this->addColonne( $etatColonne );

        $this->addColonne( new Column\OrderColumn() );

        $dictionnaireColumn = new Column\BooleanColumn('dictionnaire', 'Dictionnaire');
        $dictionnaireColumn->setValues( array( 1 => 'Présent dans le dictionnaire de référencement', 0 => 'Absent du dictionnaire') );
        $dictionnaireColumn->setSize( 100 );
        $this->addColonne( $dictionnaireColumn );

        $rechercheColumn = new Column\BooleanColumn('recherche', 'Recherche');
        $rechercheColumn->setValues( array( 1 => 'Présent dans les champs du moteur de recherche', 0 => 'Absent des champs du moteur de recherche') );
        $rechercheColumn->setSize( 100 );
        $this->addColonne( $rechercheColumn );

        $this->addColonne( new Column\LockedColumn() );

        /* Colonnes inactives */
        $this->addColonne( new Column\BlankColumn('idParent') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton('hopitalnumerique_reference_reference_show') );
        $this->addActionButton( new Action\EditButton('hopitalnumerique_reference_reference_edit') );
        // $this->addActionButton( new Action\DeleteButton('hopitalnumerique_reference_reference_delete') );

        //Boutton d'ajout d'un référentiel avec le même code par défaut
        $button = new RowAction('', 'hopitalnumerique_reference_reference_add');
        $button->setRouteParameters( array('id','mod' => 'code') );
        $button->setAttributes( array('class'=>'btn btn-warning fa fa-plus','title' => 'Ajouter comme code') );
        $this->addActionButton( $button );

        //Boutton d'ajout d'un référentiel avec parent par défaut
        $button = new RowAction('', 'hopitalnumerique_reference_reference_add');
        $button->setRouteParameters( array('id') );
        $button->setAttributes( array('class'=>'btn btn-warning fa fa-level-down','title' => 'Ajouter comme enfant') );
        $this->addActionButton( $button );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Export CSV', 'HopitalNumeriqueReferenceBundle:Reference:exportCsv') );
        $this->addMassAction( new Action\ActionMass('Supprimer', 'HopitalNumeriqueReferenceBundle:Reference:deleteMass') );
    }
}