<?php
/**
 * Configuration du grid des demandes d'intervention pour le directeur.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Directeur;

use HopitalNumerique\InterventionBundle\Grid\DemandesAbstractGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;

/**
 * Configuration du grid des demandes d'intervention pour le directeur.
 */
class SuiviDemandesGrid extends DemandesAbstractGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_intervention.manager.intervention_demande');
        $this->setFunctionName('getGridDonnees_DirecteurSuiviDemandes');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('- Aucune intervention à afficher -');
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
        
        $colonneAmbassadeurInformations = new Column\TextColumn('ambassadeurInformations', 'Ambassadeur');
        $colonneAmbassadeurInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneAmbassadeurInformations);
        
        $colonneInterventionInitiateurType = new Column\TextColumn('interventionInitiateurType', 'Initiateur');
        $colonneInterventionInitiateurType->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneInterventionInitiateurType);
        
        $colonneDateCreationLibelle = new Column\TextColumn('dateCreationLibelle', 'Création');
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

        $colonneEvaluation = new Column\TextColumn('evaluationEtatId', 'Éval.');
        $colonneEvaluation->setFilterable(false)->setSortable(false);
        $colonneEvaluation->setAlign('center');
        $colonneEvaluation->manipulateRenderCell(
            function($value, $row, $router) {
                if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId())
                {
                    return '<button class="btn btn-warning btn-xs" data-evaluation-demande="'.$row->getField('id').'" title="Envoyer une relance"><span class="glyphicon glyphicon-send"></span></button>';
                }
                else if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId())
                {
                    return '<a class="btn btn-info btn-xs" href="'.$router->generate('hopital_numerique_intervention_evaluation_voir', array('interventionDemande' => $row->getField('id'))).'"><span class="glyphicon glyphicon-eye-open"></span></a>';
                }
                return '';
            }
        );
        $this->addColonne($colonneEvaluation);

        //$this->addColonne(new Column\TextColumn('remboursementEtatLibelle', 'Remboursement'));

        /*$colonneRemboursementMontant = new Column\TextColumn('remboursementMontant', 'Montant');
        $colonneRemboursementMontant->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneRemboursementMontant);*/
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
