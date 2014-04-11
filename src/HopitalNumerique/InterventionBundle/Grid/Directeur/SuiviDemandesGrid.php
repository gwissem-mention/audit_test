<?php
/**
 * Configuration du grid des demandes d'intervention pour le directeur.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Directeur;

use HopitalNumerique\InterventionBundle\Grid\DemandesAbstractGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

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
        parent::setConfig();
        $this->setFunctionName('getGridDonneesDirecteurSuiviDemandes');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        parent::setColumns();
        
        $this->addColonneDemandeur();
        $this->addColonneAmbassadeur();
        $this->addColonneInterventionInitiateurType();
        $this->addColonneDateCreation();
        $this->addColonneInterventionEtat();
        $this->addColonneDateChoix();
        $this->addColonneEvaluationAvecEnvoiRelance();
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
