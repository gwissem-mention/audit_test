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
        
        $this->ignoreColonnes(array('interventionInitiateurId', 'nombreDemandesRegroupees', 'nombreDemandesPrincipales', 'ambassadeurRegionLibelle', 'referentEtablissementNom', 'referentEtablissementFiness', 'referentRegionLibelle', 'cmsiDateChoixLibelle', 'ambassadeurDateChoixLibelle'));
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $utilisateurConnecte = $this->utilisateurConnecte;
        
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
        
        $colonneCmsiInformations = new Column\TextColumn('cmsi_nom', 'CMSI');
        $colonneCmsiInformations->manipulateRenderCell(
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellCmsi($value, $row, $router);
            }
        );
        $this->addColonne($colonneCmsiInformations);
        
        $colonneAmbassadeurInformations = new Column\TextColumn('ambassadeur_nom', 'Ambassadeur');
        $colonneAmbassadeurInformations->manipulateRenderCell(
            function($value, $row, $router) {
                return DemandesAbstractGrid::renderCellAmbassadeur($value, $row, $router);
            }
        );
        $this->addColonne($colonneAmbassadeurInformations);
        
        $colonneDemandeurInformations = new Column\TextColumn('referent_nom', 'Demandeur');
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

        $colonneEvaluation = new Column\TextColumn('evaluationEtatId', 'Éval.');
        $colonneEvaluation->setFilterable(false)->setSortable(false);
        $colonneEvaluation->setAlign('center');
        $colonneEvaluation->manipulateRenderCell(
            function($value, $row, $router) use ($utilisateurConnecte)
            {
                if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId())
                {
                    if ($row->getField('referentId') == $utilisateurConnecte->getId())
                        return '<a class="btn btn-warning glyphicon glyphicon-edit" title="Évaluer la demande" href="'.$router->generate('hopital_numerique_intervention_admin_evaluation_nouveau', array('interventionDemande' => $row->getField('id'))).'"></a>';
                    return '<span title="À évaluer" class="glyphicon glyphicon-time"></span>';
                }
                else if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId())
                {
                    return '<a class="btn btn-info glyphicon glyphicon-eye-open" href="'.$router->generate('hopital_numerique_intervention_admin_evaluation_voir', array('interventionDemande' => $row->getField('id'))).'"></a>';
                }
                return '';
            }
        );
        $this->addColonne($colonneEvaluation);
        
        $colonneRemboursementEtatLibelle = new Column\TextColumn('remboursementEtatLibelle', 'Remboursement');
        $this->addColonne($colonneRemboursementEtatLibelle);
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new Action\EditButton('hopital_numerique_intervention_admin_demande_edit'));
        $this->addActionButton(new Action\ShowButton('hopital_numerique_intervention_admin_demande_voir'));
        $this->addActionButton(new Action\DeleteButton('hopital_numerique_intervention_admin_demande_delete'));
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        $utilisateurConnecte = $this->_container->get('security.context')->getToken()->getUser();
        
        if ($this->_container->get('nodevo_acl.manager.acl')->checkAuthorization($this->_container->get('router')->generate('hopital_numerique_intervention_admin_demande_delete', array('id' => 0)), $utilisateurConnecte) != -1)
            $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueInterventionBundle:Admin/Demande:gridSupprimeMass') );
    }
}
