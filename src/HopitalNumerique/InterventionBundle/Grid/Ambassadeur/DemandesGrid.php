<?php
/**
 * Configuration du grid des demandes d'intervention pour l'ambassadeur.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Ambassadeur;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;

/**
 * Configuration du grid des demandes d'intervention pour l'ambassadeur.
 */
class DemandesGrid extends Grid implements IGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_intervention.manager.intervention_demande');
        $this->setFunctionName('getGridDonnees_AmbassadeurDemandes');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('- Aucune intervention à afficher -');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $colonneDemandeurInformations = new Column\TextColumn('demandeurInformations', 'Demandeur');
        $colonneDemandeurInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDemandeurInformations);
        
        $colonneInterventionInitiateurType = new Column\TextColumn('interventionInitiateurType', 'Initiateur');
        $colonneInterventionInitiateurType->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneInterventionInitiateurType);
        
        $colonneDateCreationLibelle = new Column\DateColumn('dateCreationLibelle', 'Création');
        $colonneDateCreationLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDateCreationLibelle);
        
        $colonneInterventionEtatLibelle = new Column\TextColumn('interventionEtatLibelle', 'État');
        $colonneInterventionEtatLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneInterventionEtatLibelle);
       
        $colonneCmsiDateChoixLibelle = new Column\DateColumn('cmsiDateChoixLibelle', 'CMSI');
        $colonneCmsiDateChoixLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneCmsiDateChoixLibelle);
       
        $colonneAmbassadeurDateChoixLibelle = new Column\DateColumn('ambassadeurDateChoixLibelle', 'Ambassadeur');
        $colonneAmbassadeurDateChoixLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneAmbassadeurDateChoixLibelle);

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

        $colonneRemboursementMontant = new Column\TextColumn('remboursementMontant', 'Montant');
        $colonneRemboursementMontant->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneRemboursementMontant);
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
