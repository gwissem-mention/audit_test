<?php

namespace HopitalNumerique\GlossaireBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Glossaire.
 */
class GlossaireGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Glossaire.
     */
    public function setConfig()
    {
        $this->setSource( 'HopitalNumeriqueGlossaireBundle:Glossaire' );
        $this->setNoDataMessage('Aucun élément du glossaire à afficher.');
        $this->setDefaultOrder('mot', 'ASC');
    }

    /**
     * Ajoute les colonnes du grid Glossaire.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('mot', 'Mot') );
        
        $domaineColumn = new Column\TextColumn('domaines', 'Domaine(s) associé(s)');
        $domaineColumn->setFilterType('select');
        $domaineColumn->setSelectFrom('values');
        $domaineColumn->setOperatorsVisible( false );
        $this->addColonne( $domaineColumn );

        $this->addColonne( new Column\TextColumn('intitule', 'Intitule') );
        
        $sensitive = new Column\BooleanColumn('sensitive', 'Sensible à la casse');
        $sensitive->setSize(130);
        $this->addColonne($sensitive);

        /* Colonnes inactives */
        $this->addColonne( new Column\BlankColumn('description') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_glossaire_glossaire_show' ) );
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_glossaire_glossaire_edit' ) );
        //$this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_glossaire_glossaire_delete' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Export CSV', 'HopitalNumeriqueGlossaireBundle:Glossaire:exportCsv') );
        $this->addMassAction( new Action\ActionMass('Supprimer', 'HopitalNumeriqueGlossaireBundle:Glossaire:deleteMass') );
    }
}
