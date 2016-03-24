<?php

namespace HopitalNumerique\ObjetBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Objet.
 */
class ObjetGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Objet.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_objet.manager.objet' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun Objet à afficher.');
        $this->showIDColumn( false );
    }

    public function setDefaultFiltreFromController($filtre)
    {
        $filtres = array();

        $this->setPersistence(false);

        switch ($filtre) {
            case 'point-dur':
                $filtres['types'] = 'Point dur';
                break;
            case 'production':
                $filtres['types'] = 'Article';
                break;
            case 'non-publie':
                $filtres['etat'] = 'Inactif';
                break;
        }

        $this->setDefaultFilters($filtres);
    }

    /**
     * Ajoute les colonnes du grid Objet.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\NumberColumn('idObjet', 'ID') );
        $this->addColonne( new Column\TextColumn('titre', 'Titre') );
        $this->addColonne( new Column\TextColumn('types','Catégories') );
        $this->addColonne( new Column\TextColumn('domaineNom','Domaine(s) associé(s)') );
        
        $infraColumn = new Column\BooleanColumn('isInfraDoc', 'Infra-doc ?');
        $infraColumn->setSize( 90 );
        $this->addColonne( $infraColumn );

        $isArticleColumn = new Column\BooleanColumn('isArticle', 'Article ?');
        $isArticleColumn->setVisible(false);
        $isArticleColumn->setFilterable(false);
        $this->addColonne($isArticleColumn);

        $etatColonne = new Column\TextColumn('etat', 'Etat');
        $etatColonne->setSize( 80 );
        $etatColonne->setFilterType('select');
        $etatColonne->setSelectFrom('source');
        $etatColonne->setOperatorsVisible( false );
        $etatColonne->setDefaultOperator( \APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ );
        $this->addColonne( $etatColonne );

        $this->addColonne( new Column\DateColumn('dateCreation', 'Date de création') );

        $this->addColonne( new Column\NumberColumn('nbVue', 'Nombre de vues') );
        
        $this->addColonne( new Column\NumberColumn('moyenne', 'Note moyenne') );

        $this->addColonne( new Column\NumberColumn('nbNotes', 'Nombre de notes') );

        $this->addColonne( new Column\LockedColumn() );

        /* Colonnes inactives */
        $this->addColonne( new Column\BlankColumn('lockedBy') );
        $this->addColonne( new Column\BlankColumn('dateModification') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_objet_objet_show' ) );
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_objet_objet_edit' ) );

        //Goto Refs
        $referencesButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_objet_objet_edit');
        $referencesButton->setRouteParameters( array('id', 'infra'=>0, 'toRef'=>1) );
        $referencesButton->setAttributes( array('class'=>'btn btn-primary fa fa-cog','title' => 'Accès direct aux références') );
        $referencesButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row $row) {
            return !$row->getField('isArticle') ? $action : null;
        });
        $this->addActionButton( $referencesButton );

        //Custom Unlock button : Affiche le bouton dévérouillé si la ligne est vérouillée
        $unlockButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_objet_objet_cancel');
        $unlockButton->setRouteParameters( array('id', 'message'=>true) );
        $unlockButton->setAttributes( array('class'=>'btn btn-warning fa fa-unlock','title' => 'Déverrouiller') );
        $unlockButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row $row) {
            return $row->getField('lock') ? $action : null;
        });
        $this->addActionButton( $unlockButton );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Export CSV', 'HopitalNumeriqueObjetBundle:Export:exportCsv') );
        $this->addMassAction( new Action\ActionMass('Supprimer', 'HopitalNumeriqueObjetBundle:Objet:deleteMass') );
    }
}
