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
        $this->setNoDataMessage('Aucune intervention à afficher.');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $this->addColonne(new Column\TextColumn('demandeurInformations', 'Demandeur'));
        $this->addColonne(new Column\TextColumn('interventionInitiateurType', 'Initiateur'));
        $this->addColonne(new Column\DateColumn('dateCreationLibelle', 'Création'));
        $this->addColonne(new Column\TextColumn('interventionEtatLibelle', 'État'));
        $this->addColonne(new Column\DateColumn('cmsiDateChoixLibelle', 'Choix CMSI'));
        $this->addColonne(new Column\DateColumn('ambassadeurDateChoixLibelle', 'Choix ambassadeur'));
        
        
        $colonneEvaluation = new Column\TextColumn('evaluationEtatId', 'Éval.');
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

        $this->addColonne(new Column\TextColumn('remboursementEtatLibelle', 'Remboursement'));
        $this->addColonne(new Column\TextColumn('remboursementMontant', 'Montant'));
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
