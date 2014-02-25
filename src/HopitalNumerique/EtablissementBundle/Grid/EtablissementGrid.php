<?php 
namespace HopitalNumerique\EtablissementBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du Grid Etablissement
 */
class EtablissementGrid extends Grid implements IGrid
{
    /**
     * Set la config propre au Grid (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_etablissement.manager.etablissement' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('nom', 'Nom') );

        $finessColonne = new Column\TextColumn('finess', 'FINESS Geographique');
        $finessColonne->setSize( 170 );
        $finessColonne->setSortable( false );
        $this->addColonne( $finessColonne );

        $typeColonne = new Column\TextColumn('typeOrganisme', 'Type d\'organisme');
        $typeColonne->setSize( 140 );
        $this->addColonne( $typeColonne );

        $regionColonne = new Column\TextColumn('region', 'Région');
        $regionColonne->setSize( 150 );
        $this->addColonne( $regionColonne );

        $departementColonne = new Column\TextColumn('departement', 'Département');
        $departementColonne->setSize( 200 );
        $this->addColonne( $departementColonne );

        $villeColonne = new Column\TextColumn('ville', 'Ville');
        $villeColonne->setSize( 150 );
        $this->addColonne( $villeColonne );

        $codepostalColonne = new Column\TextColumn('codepostal', 'Code Postal');
        $codepostalColonne->setSize( 110 );
        $this->addColonne( $codepostalColonne );
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton('hopitalnumerique_etablissement_edit') );
        $this->addActionButton( new Action\ShowButton('hopitalnumerique_etablissement_show') );
        $this->addActionButton( new Action\DeleteButton('hopitalnumerique_etablissement_delete') );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        
    }
}