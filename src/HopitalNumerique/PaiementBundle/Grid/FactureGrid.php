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
        $this->setNoDataMessage('Aucune facture à afficher.');
        $this->setButtonSize( 43 );
        $this->setDefaultOrder( 'dateCreation', 'desc' );
    }

    /**
     * Ajoute les colonnes du grid Facture.
     */
    public function setColumns()
    {
        $nameColumn = new Column\TextColumn('name', 'NAME');
        $nameColumn->setFilterable(false)->setVisible(false);
        $this->addColonne($nameColumn);

        $datePaiementColumn = new Column\TextColumn('datePaiement', 'DATE PAIEMENT');
        $datePaiementColumn->setFilterable(false)->setVisible(false);
        $this->addColonne($datePaiementColumn);

        $this->addColonne( new Column\AssocColumn('user.nom', 'Nom') );
        $this->addColonne( new Column\AssocColumn('user.prenom', 'Prénom') );
        $this->addColonne( new Column\AssocColumn('user.email', 'Adresse e-mail') );
        $this->addColonne( new Column\AssocColumn('user.region.libelle', 'Région') );
        $this->addColonne( new Column\AssocColumn('user.etablissementRattachementSante.nom', 'Établissement') );
        $this->addColonne( new Column\TextColumn('total', 'Total') );
        $this->addColonne( new Column\TextColumn('id', 'Numéro de facture') );

        $factureAnnuleeColumn = new Column\AssocColumn('factureAnnulee.facture.name', 'Facture abandonnée');
        $this->addColonne($factureAnnuleeColumn);
        
        $payedColumn = new Column\BooleanColumn('payee', 'Payé');
        $payedColumn->setSize( 60 );
        $this->addColonne( $payedColumn );

        $etatColumn = new Column\BooleanColumn('annulee', 'Facture Annulé');
        $etatColumn->setSize( 60 );
        $this->addColonne( $etatColumn );
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
            return (!$row->getField('payee') && !$row->getField('annulee')) ? $action : null;

        });
        $this->addActionButton( $payeButton );

        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_paiement_facture_detail' ) );

        // Bouton pour désannuler la facture, s'affiche que si la facture est annulée
        $etatButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_paiement_change_etat');
        $etatButton->setRouteParameters( array('id') );
        $etatButton->setAttributes( array('class'=>'btn btn-primary fa fa-check','title' => 'désannuler', 'onclick' => 'return confirm(\'Confirmer la désannulation de la facture ?\');'));
        $etatButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row $row) {
            return $row->getField('annulee') ? $action : null;

        });
        $this->addActionButton( $etatButton );

        // Bouton pour annuler la facture, s'affiche que si la facture n'est pas annulée
        $annuleButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_paiement_change_etat');
        $annuleButton->setAttributes( array('class'=>'btn btn-warning fa fa-times','title' => 'Annuler', 'onclick' => 'return confirm(\'Confirmer l\\\'annulation de la facture ?\');'));
        $annuleButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row  $row) {
            return $row->getField('annulee') ? null : $action ;
        });
        $this->addActionButton( $annuleButton );

        $cancelButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_paiement_facture_cancel');
        $cancelButton->setRouteParameters(['id']);
        $cancelButton->setAttributes(array('class'=>'btn btn-danger fa fa-times', 'title' => 'abandonnée', 'onclick' => 'return confirm(\'Confirmer l\\\'abandon de la facture ?\');'));
        $cancelButton->manipulateRender(function ($action, \APY\DataGridBundle\Grid\Row $row) {
            return (!$row->getEntity()->hasBeenCanceled() ? $action : null);
        });
        $this->addActionButton($cancelButton);
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
    }
}
