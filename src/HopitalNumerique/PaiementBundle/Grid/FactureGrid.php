<?php

namespace HopitalNumerique\PaiementBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Facture.
 */
class FactureGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Facture.
     */
    public function setConfig()
    {
        $this->setSource( 'HopitalNumeriquePaiementBundle:Facture' );
        $this->setNoDataMessage('Aucun Facture à afficher.');
        $this->setButtonSize( 43 );
        $this->setDefaultOrder( 'dateCreation', 'desc' );
    }

    /**
     * Ajoute les colonnes du grid Facture.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\AssocColumn('user.nom', 'Nom') );
        $this->addColonne( new Column\AssocColumn('user.prenom', 'Prénom') );
        $this->addColonne( new Column\AssocColumn('user.email', 'Adresse e-mail') );
        $this->addColonne( new Column\AssocColumn('user.region.libelle', 'Région') );
        $this->addColonne( new Column\AssocColumn('user.etablissementRattachementSante.nom', 'Établissement') );
        $this->addColonne( new Column\TextColumn('total', 'Total') );
        
        $payedColumn = new Column\BooleanColumn('payee', 'Facture Payée ?');
        $payedColumn->setSize( 120 );
        $this->addColonne( $payedColumn );

        /* Colonnes invisibles */
        $this->addColonne( new Column\BlankColumn('dateCreation') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $downloadButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_paiement_facture_export');
        $downloadButton->setRouteParameters( array('id') );
        $downloadButton->setAttributes( array('class'=>'btn btn-info fa fa-download','title' => 'Télécharger la facture') );
        $this->addActionButton( $downloadButton );

        $payeButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_paiement_facture_paye');
        $payeButton->setRouteParameters( array('id') );    
        $payeButton->setAttributes( array('class'=>'btn btn-green fa fa-money','title' => 'Payer') );
        $payeButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row $row) {
            return !$row->getField('payee') ? $action : null;
        });
        $this->addActionButton( $payeButton );
        
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_paiement_facture_detail' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
    }
}