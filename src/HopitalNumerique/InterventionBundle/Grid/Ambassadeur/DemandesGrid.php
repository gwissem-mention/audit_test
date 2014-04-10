<?php
/**
 * Configuration du grid des demandes d'intervention pour l'ambassadeur.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Ambassadeur;

use HopitalNumerique\InterventionBundle\Grid\DemandesAbstractGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;

/**
 * Configuration du grid des demandes d'intervention pour l'ambassadeur.
 */
class DemandesGrid extends DemandesAbstractGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut)
     */
    public function setConfig()
    {
        parent::setConfig();
        $this->setFunctionName('getGridDonneesAmbassadeurDemandes');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        parent::setColumns();
        
        $this->addColonneDemandeur();
        $this->addColonneInterventionInitiateurType();
        $this->addColonneDateCreation();
        $this->addColonneInterventionEtat();
        $this->addColonneDateChoix();

        $colonneEvaluation = new Column\TextColumn('evaluationEtatId', 'Éval.');
        $colonneEvaluation->setFilterable(false)->setSortable(false);
        $colonneEvaluation->setAlign('center');
        $colonneEvaluation->manipulateRenderCell(
            function($value, $row, $router)
            {
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
