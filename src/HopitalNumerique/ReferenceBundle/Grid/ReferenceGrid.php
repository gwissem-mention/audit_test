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
        $this->setFilterIdColumn( false );
    }

    /**
     * Ajoute les colonnes du grid Reference.
     */
    public function setColumns()
    {
        $this->addColonne(new Column\TextColumn('idReference', 'ID'));

        $this->addColonne(new Column\TextColumn('libelle', 'Libellé du concept'));

        $domaineColumn = new Column\TextColumn('domaineNom', 'Domaine(s)');
        $domaineColumn->setSize(150);
        $this->addColonne($domaineColumn);

        $dictionnaireColumn = new Column\BooleanColumn('reference', 'Est une référence');
        $dictionnaireColumn->setValues([1 => 'Oui', 0 => 'Non']);
        $dictionnaireColumn->setSize(100);
        $this->addColonne($dictionnaireColumn);

        $rechercheColumn = new Column\BooleanColumn('inRecherche', 'Présent dans la recherche');
        $rechercheColumn->setValues([1 => 'Oui', 0 => 'Non']);
        $rechercheColumn->setSize(100);
        $this->addColonne($rechercheColumn);

        $etatColonne = new Column\TextColumn('etat', 'Etat');
        $etatColonne->setSize(80);
        $etatColonne->setFilterType('select');
        $etatColonne->setSelectFrom('source');
        $etatColonne->setOperatorsVisible(false);
        $etatColonne->setDefaultOperator(\APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ);
        $this->addColonne($etatColonne);

        $inGlossaireColumn = new Column\BooleanColumn('inGlossaire', 'Actif dans le glossaire');
        $inGlossaireColumn->setValues([1 => 'Oui', 0 => 'Non']);
        $inGlossaireColumn->setSize(100);
        $this->addColonne($inGlossaireColumn);

        $this->addColonne(new Column\TextColumn('code', 'Code'));

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