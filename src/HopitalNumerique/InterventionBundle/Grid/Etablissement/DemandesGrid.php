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
        $this->setSource('hopitalnumerique_intervention.manager.intervention_demande');
        $this->setFunctionName('getGridDonnees_EtablissementDemandes');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('- Aucune intervention à afficher -');
        $this->setDefaultLimit( 1000);
        $this->setLimits( array(5, 10, 15, 1000) );
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
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellInitiateur($value, $row, $router);
            }
        );
        $this->addColonne($colonneInitiateur->setFilterable(false)->setSortable(false));

        $colonneAmbassadeur = new Column\TextColumn('ambassadeurInformations', 'Ambassadeur');
        $colonneCreation = new Column\DateColumn('dateCreationLibelle', 'Création');
        $colonneEtat = new Column\TextColumn('interventionEtatLibelle', 'État');
        
        $this->addColonne($colonneAmbassadeur->setFilterable(false)->setSortable(false));       
        $this->addColonne($colonneCreation->setFilterable(false)->setSortable(false));       
        $this->addColonne($colonneEtat->setFilterable(false)->setSortable(false));

        $colonneDateChoix = new Column\TextColumn('dateChoix', 'Date choix');
        $colonneDateChoix->setFilterable(false)->setSortable(false);
        $colonneDateChoix->manipulateRenderCell(
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellDateChoix($value, $row, $router);
            }
        );
        $this->addColonne($colonneDateChoix);

        $colonneEvaluation = new Column\TextColumn('evaluationEtatId', 'Éval.');
        $colonneEvaluation->setAlign('center');
        $colonneEvaluation->manipulateRenderCell(
            function($value, $row, $router)
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
