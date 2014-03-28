<?php
/**
 * Configuration du grid des demandes d'intervention pour l'ambassadeur.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Admin;

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
        $this->setFunctionName('getGridDonneesAdminDemandes');
        
        $this->ignoreColonnes(array('interventionInitiateurId', 'nombreDemandesRegroupees', 'nombreDemandesPrincipales', 'cmsiNom', 'cmsiPrenom', 'ambassadeurNom', 'ambassadeurPrenom', 'ambassadeurRegionLibelle', 'referentNom', 'referentPrenom', 'referentEtablissementNom', 'referentEtablissementFiness', 'referentRegionLibelle', 'cmsiDateChoixLibelle', 'ambassadeurDateChoixLibelle'));
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        parent::setColumns();
        
        $colonneInterventionInitiateurType = new Column\TextColumn('interventionInitiateurType', '');
        $colonneInterventionInitiateurType->setAlign('center');
        $colonneInterventionInitiateurType->setFilterable(false);
        $colonneInterventionInitiateurType->manipulateRenderCell(
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellInitiateur($value, $row, $router);
            }
        );
        $this->addColonne($colonneInterventionInitiateurType);
        
        $colonneDateCreationLibelle = new Column\DateColumn('dateCreationLibelle', 'Création');
        $this->addColonne($colonneDateCreationLibelle);
        
        $colonneInterventionEtatLibelle = new Column\TextColumn('interventionEtatLibelle', 'État');
        $this->addColonne($colonneInterventionEtatLibelle);
        
        $colonneCmsiInformations = new Column\TextColumn('cmsiInformations', 'CMSI');
        $colonneCmsiInformations->manipulateRenderCell(
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellCmsi($value, $row, $router);
            }
        );
        $this->addColonne($colonneCmsiInformations);
        
        $colonneAmbassadeurInformations = new Column\TextColumn('ambassadeurInformations', 'Ambassadeur');
        $colonneAmbassadeurInformations->manipulateRenderCell(
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellAmbassadeur($value, $row, $router);
            }
        );
        $this->addColonne($colonneAmbassadeurInformations);
        
        $colonneDemandeurInformations = new Column\TextColumn('demandeurInformations', 'Demandeur');
        $colonneDemandeurInformations->manipulateRenderCell(
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellReferent($value, $row, $router);
            }
        );
        $this->addColonne($colonneDemandeurInformations);
        
        $colonneObjetsInformations = new Column\TextColumn('objetsInformations', 'Objets');
        $colonneObjetsInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneObjetsInformations);

        $colonneInterventionTypeLibelle = new Column\TextColumn('interventionTypeLibelle', 'État');
        $this->addColonne($colonneInterventionTypeLibelle);
       
        $colonneDateChoix = new Column\TextColumn('dateChoix', 'Date choix');
        $colonneDateChoix->manipulateRenderCell(
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellDateChoix($value, $row, $router);
            }
        );
        $this->addColonne($colonneDateChoix);
        
        $colonneEvaluationEtatLibelle = new Column\TextColumn('evaluationEtatLibelle', 'Éval.');
        $this->addColonne($colonneEvaluationEtatLibelle);
        
        $colonneRemboursementEtatLibelle = new Column\TextColumn('remboursementEtatLibelle', 'Remboursement');
        $this->addColonne($colonneRemboursementEtatLibelle);
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new Action\EditButton('hopital_numerique_intervention_admin_demande_edit'));
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueInterventionBundle:Admin/Demande:gridSupprimeMass') );
    }
}
