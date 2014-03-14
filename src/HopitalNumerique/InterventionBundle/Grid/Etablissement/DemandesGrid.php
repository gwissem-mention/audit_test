<?php
/**
 * Configuration du grid des demandes d'intervention pour l'établissement.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Etablissement;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;
use HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur;

/**
 * Configuration du grid des demandes d'intervention pour l'établissement.
 */
class DemandesGrid extends Grid implements IGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_intervention.manager.intervention_demande');
        $this->setFunctionName('getGridDonnees_EtablissementDemandes');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('Aucune intervention à afficher.');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $colonneInitiateur = new Column\TextColumn('interventionInitiateurId', '');
        $colonneInitiateur->setAlign('center');
        $colonneInitiateur->manipulateRenderCell(
                function($value, $row, $router) {
                    if ($row->getField('interventionInitiateurId') == InterventionInitiateur::getInterventionInitiateurCmsiId())
                    {
                        return '<span class="glyphicon glyphicon-user" title="Initié par '.$row->getField('interventionInitiateurType').'"></span>';
                    }
                    else if ($row->getField('interventionInitiateurId') == InterventionInitiateur::getInterventionInitiateurEtablissementId())
                    {
                        return '<span class="glyphicon glyphicon-home" title="Initié par '.$row->getField('interventionInitiateurType').'"></span>';
                    }
                    return '';
                }
        );
        $this->addColonne($colonneInitiateur);
        
        $this->addColonne(new Column\TextColumn('ambassadeurInformations', 'Ambassadeur'));
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
                    return '<a class="btn btn-warning btn-xs" href="'.$router->generate('hopital_numerique_intervention_evaluation_nouveau', array('interventionDemande' => $row->getField('id'))).'"><span class="glyphicon glyphicon-edit"></span></a>';
                }
                else if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId())
                {
                    return '<a class="btn btn-success btn-xs" href="'.$router->generate('hopital_numerique_intervention_evaluation_voir', array('interventionDemande' => $row->getField('id'))).'"><span class="glyphicon glyphicon-check"></span></a>';
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
