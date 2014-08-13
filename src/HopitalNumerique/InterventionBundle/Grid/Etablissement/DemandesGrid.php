<?php
/**
 * Configuration du grid des demandes d'intervention pour l'établissement.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Etablissement;

use HopitalNumerique\InterventionBundle\Grid\DemandesAbstractGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;

/**
 * Configuration du grid des demandes d'intervention pour l'établissement.
 */
class DemandesGrid extends DemandesAbstractGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut)
     */
    public function setConfig()
    {
        parent::setConfig();
        $this->setFunctionName('getGridDonneesEtablissementDemandes');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        parent::setColumns();

        $colonneInitiateur = new Column\TextColumn('interventionInitiateurId', '');
        $colonneInitiateur->setAlign('center');
        $colonneInitiateur->manipulateRenderCell(
            function($value, \APY\DataGridBundle\Grid\Row $row) {
                return DemandesAbstractGrid::renderCellInitiateur($value, $row);
            }
        );
        $this->addColonne($colonneInitiateur->setFilterable(false)->setSortable(false));

        $this->addColonneAmbassadeur();
        $this->addColonneDateCreation();
        $this->addColonneInterventionEtat();
        $this->addColonneDateChoix();

        $colonneEvaluation = new Column\TextColumn('evaluationEtatId', 'Éval.');
        $colonneEvaluation->setAlign('center');
        $colonneEvaluation->manipulateRenderCell(
            function($value, \APY\DataGridBundle\Grid\Row $row, \Symfony\Component\Routing\Router $router)
            {
                if (intval($row->getField('nombreDemandesPrincipales')) == 0)
                {
                    if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId())
                    {
                        return '<a class="btn btn-warning" title="Évaluer la demande" href="'.$router->generate('hopital_numerique_intervention_evaluation_nouveau', array('interventionDemande' => $row->getField('id'))).'"><span class="glyphicon glyphicon-edit"></span></a>';
                    }
                    else if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId())
                    {
                        return '<a class="btn btn-success" title="Voir l\'évaluation" href="'.$router->generate('hopital_numerique_intervention_evaluation_voir', array('interventionDemande' => $row->getField('id'))).'"><span class="glyphicon glyphicon-check"></span></a>';
                    }
                }
                else
                {
                    if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId())
                    {
                        return '<button disabled class="btn btn-success"><span class="glyphicon glyphicon-check"></span></button>';
                    }
                }
                return '';
            }
        );
        $this->addColonne($colonneEvaluation->setFilterable(false)->setSortable(false));
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
