<?php

namespace HopitalNumerique\AutodiagBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Resultat.
 */
class ResultatGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Resultat.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_autodiag.manager.resultat' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun résultat à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Resultat.
     */
    public function setColumns()
    {

        $autodiagColumn = new Column\TextColumn('outil', 'Nom de l\'autodiagnostic');
        $autodiagColumn->setFilterable( false );
        $this->addColonne( $autodiagColumn );

        $userColumn = new Column\TextColumn('user', 'Prénom et Nom de l\'utilisateur');
        $userColumn->setFilterable( false );
        $this->addColonne( $userColumn );

        $etablissementColumn = new Column\TextColumn('etablissement', 'Etablissement de rattachement');
        $etablissementColumn->setFilterable( false );
        $this->addColonne( $etablissementColumn );

        $sharedForColumn = new Column\TextColumn('sharedFor', 'Partagé vers');
        $sharedForColumn->setFilterable( false );
        $this->addColonne( $sharedForColumn );

        $sharedByColumn = new Column\TextColumn('sharedBy', 'Partagé par');
        $sharedByColumn->setFilterable( false );
        $this->addColonne( $sharedByColumn );

        $tauxColumn = new Column\TextColumn('taux', 'Taux de remplissage global');
        $tauxColumn->setFilterable( false );
        $tauxColumn->setSize( 180 );
        $this->addColonne( $tauxColumn );

        $lastSaveColumn = new Column\DateColumn('lastSave', 'Date du dernier enregistrement');
        $lastSaveColumn->setFilterable( false );
        $lastSaveColumn->setSize( 200 );
        $this->addColonne( $lastSaveColumn );

        $validationColumn = new Column\DateColumn('validation', 'Date de validation');
        $validationColumn->setFilterable( false );
        $validationColumn->setSize( 130 );
        $this->addColonne( $validationColumn );

        $syntheseColumn = new Column\BooleanColumn('synthese', 'Synthèse ?');
        $syntheseColumn->setSize( 90 );
        $this->addColonne( $syntheseColumn );

        //Colonnes invisible
        $this->addColonne( new Column\BlankColumn('id') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_autodiag_resultat_detail' ) );

        //Custom Download button : Affiche le bouton si le fichier PDF exist
        $dlPdfButton = new Action\DownloadButton( 'hopitalnumerique_autodiag_resultat_pdf' );
        $dlPdfButton->addAttribute('title', 'Télécharger le PDF');
        $dlPdfButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row  $row) {
        	return $row->getField('pdfExist') ? $action : null;
        });
        $this->addActionButton( $dlPdfButton );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Export CSV'  ,'HopitalNumeriqueAutodiagBundle:Resultat:exportCSV') );
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueAutodiagBundle:Resultat:deleteMass') );
    }
}