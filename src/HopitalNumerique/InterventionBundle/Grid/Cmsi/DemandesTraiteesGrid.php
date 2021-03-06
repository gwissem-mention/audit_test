<?php
/**
 * Configuration du grid des demandes d'intervention traitées pour le CMSI.
 */

namespace HopitalNumerique\InterventionBundle\Grid\Cmsi;

use HopitalNumerique\InterventionBundle\Grid\DemandesAbstractGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;

/**
 * Configuration du grid des demandes d'intervention traitées pour le CMSI.
 */
class DemandesTraiteesGrid extends DemandesAbstractGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut).
     */
    public function setConfig()
    {
        parent::setConfig();
        $this->setFunctionName('getGridDonneesCmsiDemandesTraitees');
    }

    /**
     * Ajoute les colonnes visibles du grid.
     */
    public function setColumns()
    {
        $utilisateurConnecte = $this->utilisateurConnecte;

        parent::setColumns();

        $this->addColonneDemandeur();
        $this->addColonneInterventionInitiateurType();
        $this->addColonneAmbassadeur();
        $this->addColonneDateCreation();
        $this->addColonneInterventionEtat();
        $this->addColonneDateChoix();

        $colonneEvaluation = new Column\TextColumn('evaluationEtatId', 'Éval.');
        $colonneEvaluation->setFilterable(false)->setSortable(false);
        $colonneEvaluation->setAlign('center');
        $colonneEvaluation->manipulateRenderCell(
            function ($value, \APY\DataGridBundle\Grid\Row $row, \Symfony\Component\Routing\Router $router) use ($utilisateurConnecte) {
                if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId()) {
                    if ($row->getField('referentId') == $utilisateurConnecte->getId()) {
                        return '<a class="btn btn-warning" title="Évaluer la demande" href="' . $router->generate('hopital_numerique_intervention_evaluation_nouveau', ['interventionDemande' => $row->getField('id')]) . '"><span class="glyphicon glyphicon-edit"></span></a>';
                    }

                    return '<span title="À évaluer" class="glyphicon glyphicon-time"></span>';
                } elseif ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId()) {
                    return '<a class="btn btn-info btn-xs" href="' . $router->generate('hopital_numerique_intervention_evaluation_voir', ['interventionDemande' => $row->getField('id')]) . '"><span class="glyphicon glyphicon-eye-open"></span></a>';
                }

                return '';
            }
        );
        $this->addColonne($colonneEvaluation);
    }

    /**
     * Ajoute les boutons d'actions si nécessaire.
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new Action\ShowButton('hopital_numerique_intervention_demande_voir'));
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
    }
}
