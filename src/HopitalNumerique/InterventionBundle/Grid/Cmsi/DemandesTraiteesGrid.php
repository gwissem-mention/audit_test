<?php
/**
 * Configuration du grid des demandes d'intervention traitées pour le CMSI.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Cmsi;

use HopitalNumerique\InterventionBundle\Grid\DemandesAbstractGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid des demandes d'intervention traitées pour le CMSI.
 */
class DemandesTraiteesGrid extends DemandesAbstractGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut)
     */
    public function setConfig()
    {
        parent::setConfig();
        $this->setFunctionName('getGridDonneesCmsiDemandesTraitees');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        parent::setColumns();

        $colonneDemandeurInformations = new Column\TextColumn('demandeurInformations', 'Demandeur');
        $colonneDemandeurInformations->manipulateRenderCell(
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellReferent($value, $row, $router);
            }
        );
        $colonneDemandeurInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDemandeurInformations);
        
        $colonneInterventionInitiateurType = new Column\TextColumn('interventionInitiateurType', 'Initiateur');
        $colonneInterventionInitiateurType->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneInterventionInitiateurType);
        
        $colonneAmbassadeurInformations = new Column\TextColumn('ambassadeurInformations', 'Ambassadeur');
        $colonneAmbassadeurInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneAmbassadeurInformations);
        
        $colonneDateCreationLibelle = new Column\DateColumn('dateCreationLibelle', 'Création');
        $colonneDateCreationLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDateCreationLibelle);
        
        $colonneInterventionEtatLibelle = new Column\TextColumn('interventionEtatLibelle', 'État');
        $colonneInterventionEtatLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneInterventionEtatLibelle);
        
        $colonneDateChoix = new Column\TextColumn('dateChoix', 'Date choix');
        $colonneDateChoix->setFilterable(false)->setSortable(false);
        $colonneDateChoix->manipulateRenderCell(
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellDateChoix($value, $row, $router);
            }
        );
        $this->addColonne($colonneDateChoix);
        
        $colonneEvaluationEtatLibelle = new Column\TextColumn('evaluationEtatLibelle', 'Éval.');
        $colonneEvaluationEtatLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneEvaluationEtatLibelle);
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new Action\ShowButton('hopital_numerique_intervention_demande_voir'));
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        
    }
}
