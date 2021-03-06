<?php

namespace HopitalNumerique\PaiementBundle\Grid;

use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Row;
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
        $this->setSource('HopitalNumeriquePaiementBundle:Facture');
        $this->setNoDataMessage('Aucune facture à afficher.');
        $this->setButtonSize(43);
        $this->setDefaultOrder('dateCreation', 'desc');
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

        $this->addColonne(new Column\AssocColumn('user.lastname', 'Nom'));
        $this->addColonne(new Column\AssocColumn('user.firstname', 'Prénom'));
        $this->addColonne(new Column\AssocColumn('user.email', 'Adresse e-mail'));
        $this->addColonne(new Column\AssocColumn('user.region.libelle', 'Région'));
        $this->addColonne(new Column\AssocColumn('user.organization.nom', 'Établissement'));
        $this->addColonne(new Column\TextColumn('total', 'Total'));
        $this->addColonne(new Column\TextColumn('id', 'Numéro de facture'));

        $payedColumn = new Column\BooleanColumn('payee', 'Payé');
        $payedColumn->setSize(60);
        $this->addColonne($payedColumn);

        $etatColumn = new Column\BooleanColumn('annulee', 'Facture Annulé');
        $etatColumn->setSize(60);
        $this->addColonne($etatColumn);
        /* Colonnes invisibles */
        $this->addColonne(new Column\BlankColumn('dateCreation'));
    }

    /**
     * Ajoute les boutons d'action.
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new Action\ShowButton('hopitalnumerique_paiement_facture_detail'));

        $downloadButton = new RowAction('', 'hopitalnumerique_paiement_facture_export');
        $downloadButton->setRouteParameters(['id']);
        $downloadButton->setAttributes(['class' => 'btn btn-info fa fa-download', 'title' => 'Télécharger la facture']);
        $this->addActionButton($downloadButton);

        $payeButton = new RowAction('', 'hopitalnumerique_paiement_facture_paye');
        $payeButton->setRouteParameters(['id']);
        $payeButton->setAttributes(['class' => 'btn btn-green fa fa-money', 'title' => 'Payer']);
        $payeButton->manipulateRender(function ($action, Row $row) {
            return (!$row->getField('payee') && !$row->getField('annulee')) ? $action : null;
        });
        $this->addActionButton($payeButton);

        // Bouton pour désannuler la facture, s'affiche que si la facture est annulée
        $etatButton = new RowAction('', 'hopitalnumerique_paiement_change_etat');
        $etatButton->setRouteParameters(['id']);
        $etatButton->setAttributes(
            [
                'class' => 'btn btn-success fa fa-check',
                'title' => 'désannuler',
                'onclick' => 'return confirm(\'Confirmer la désannulation de la facture ?\');',
            ]
        );
        $etatButton->manipulateRender(function ($action, Row $row) {
            return !$row->getEntity()->isPayee() && $row->getEntity()->isAnnulee() ? $action : null;
        });
        $this->addActionButton($etatButton);

        // Bouton pour annuler la facture, s'affiche que si la facture n'est pas annulée
        $annuleButton = new RowAction('', 'hopitalnumerique_paiement_change_etat');
        $annuleButton->setAttributes(
            [
                'class' => 'btn btn-danger fa fa-times',
                'title' => 'Annuler',
                'onclick' => 'return confirm(\'Confirmer l\\\'annulation de la facture ?\');',
            ]
        );
        $annuleButton->manipulateRender(function ($action, Row  $row) {
            return ($row->getField('payee') || $row->getEntity()->isAnnulee()) ? null : $action;
        });
        $this->addActionButton($annuleButton);
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction(
            new Action\ActionMass('Exporter les paiements', 'HopitalNumeriquePaiementBundle:Facture:exportPaymentsMass')
        );
    }
}
