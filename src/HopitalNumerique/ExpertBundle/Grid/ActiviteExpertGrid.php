<?php

namespace HopitalNumerique\ExpertBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid ActiviteExpert.
 */
class ActiviteExpertGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid ActiviteExpert.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_expert.manager.activiteexpert' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucune activité d\'expert à afficher.');
        $this->setButtonSize(49);
    }

    /**
     * Ajoute les colonnes du grid ActiviteExpert.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('titre', 'Titre') );
        $this->addColonne( new Column\TextColumn('typeActiviteLibelle', 'Type d\'activité') );
        $this->addColonne( new Column\DateColumn('dateDebut', 'Date de début') );
        $this->addColonne( new Column\DateColumn('dateFin', 'Date de fin') );
        $this->addColonne( (new Column\NumberColumn('nbVacationParExpert', 'Nombre de vacations'))->setSize(100) );
        $this->addColonne( new Column\TextColumn('experts', 'Expert(s) concerné(s)') );
        $this->addColonne( new Column\TextColumn('anapiens', 'Anapien(s)') );
        $this->addColonne( new Column\TextColumn('prestataireLibelle', 'Prestataire') );
        $this->addColonne( new Column\TextColumn('uniteLibelle', 'Unite') );
        $this->addColonne( new Column\TextColumn('etatLibelle', 'Etat') );
        $this->addColonne( new Column\BooleanColumn('etatValidation', 'Etat validation') );

    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_expert_expert_activite_show' ) );
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_expert_expert_activite_edit' ) );
        $this->addActionButton( new Action\FilsButton( 'hopitalnumerique_expert_evenement_expert' ) );

        //générer la facture pour l'activité
        $genererFactureButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_expert_expert_generer_facture');
        $genererFactureButton->setRouteParameters( array('id') );
        $genererFactureButton->setAttributes( array('class'=>'btn btn-primary fa fa-external-link','title' => 'Générer les factures') );
        $this->addActionButton( $genererFactureButton );

        $payerButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_expert_expert_payer_facture');
        $payerButton->setRouteParameters( array('id') );
        $payerButton->setAttributes( array('class'=>'btn btn-warning fa fa-money','title' => 'Payer les factures') );
        $payerButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row $row) {
            return $row->getField('etatValidation') ? null : $action;
        });
        $this->addActionButton( $payerButton );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueExpertBundle:ActiviteExpertMass:deleteMass') );
    }
}