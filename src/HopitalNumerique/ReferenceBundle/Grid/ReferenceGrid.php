<?php

namespace HopitalNumerique\ReferenceBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

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
        $this->setFilterIdColumn( false );
        $this->setButtonSize(44);
    }

    /**
     * Ajoute les colonnes du grid Reference.
     */
    public function setColumns()
    {
        $this->addColonne(new Column\NumberColumn('idReference', 'ID'));

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

        $inGlossaireColumn = new Column\BooleanColumn('inGlossaire', 'Actif dans le glossaire');
        $inGlossaireColumn->setValues([1 => 'Oui', 0 => 'Non']);
        $inGlossaireColumn->setSize(100);
        $this->addColonne($inGlossaireColumn);

        $etatColonne = new Column\TextColumn('etat', 'Etat');
        $etatColonne->setSize(80);
        $etatColonne->setFilterType('select');
        $etatColonne->setSelectFrom('source');
        $etatColonne->setOperatorsVisible(false);
        $etatColonne->setDefaultOperator(\APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ);
        $this->addColonne($etatColonne);

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