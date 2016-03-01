<?php
/**
 * Configuration du grid des demandes d'intervention pour l'ambassadeur.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Admin;

use HopitalNumerique\InterventionBundle\Grid\DemandesAbstractGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

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
        // Pour éviter que le second bouton passe au-dessous du premier
        $this->setButtonSize(43);

        $this->ignoreColonnes(array('interventionInitiateurId', 'nombreDemandesRegroupees', 'nombreDemandesPrincipales', 'ambassadeurRegionLibelle', 'referentEtablissementNom', 'referentEtablissementFiness', 'referentRegionLibelle', 'cmsiDateChoixLibelle', 'ambassadeurDateChoixLibelle'));
    }

    public function setDefaultFiltreFromController($filtre)
    {
        $filtres = array();

        $this->setPersistence(false);

        switch ($filtre) {
            case 'Interventions-demandees':
                $filtres['interventionEtatLibelle'] = 'Accepté par le CMSI';
                break;
            case 'Interventions-en-attente':
                $filtres['interventionEtatLibelle'] = 'Mise en attente par le CMSI';
                break;
            case 'Interventions-en-cours':
                $filtres['interventionEtatLibelle'] = 'Accepté par l\'ambassadeur';
                break;
        }

        $this->setDefaultFilters($filtres);
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        //parent::setColumns();

        $colonneId = new Column\NumberColumn('idIntervention', 'ID');
        $this->addColonne($colonneId);

        $colonneTypeIntervention = new Column\TextColumn('interventionTypeLibelle', '');
        $colonneTypeIntervention->setFilterable(false)->setVisible(false);
        $this->addColonne($colonneTypeIntervention);

        $colonneInterventionInitiateurType = new Column\TextColumn('', 'Initiateur');
        $colonneInterventionInitiateurType->setAlign('center');
        $colonneInterventionInitiateurType->setFilterable(false)->setSortable(false);
        $colonneInterventionInitiateurType->manipulateRenderCell(
            function($value, \APY\DataGridBundle\Grid\Row $row) {
                return DemandesAbstractGrid::renderCellInitiateur($value, $row);
            }
        );
        $this->addColonne($colonneInterventionInitiateurType);

        $colonneDateCreationLibelle = new Column\DateColumn('dateCreationLibelle', 'Création');
        $this->addColonne($colonneDateCreationLibelle);

        $colonneInterventionEtatLibelle = new Column\TextColumn('interventionEtatLibelle', 'État');
        $this->addColonne($colonneInterventionEtatLibelle);

        $colonneCmsiInformations = new Column\TextColumn('cmsi_nom', 'CMSI');
        $this->addColonne($colonneCmsiInformations);

        $colonneAmbassadeurInformations = new Column\TextColumn('ambassadeur_nom', 'Ambassadeur');
        $this->addColonne($colonneAmbassadeurInformations);

        $colonneIdDemandeur = new Column\TextColumn('referentId', '');
        $colonneIdDemandeur->setFilterable(false)->setVisible(false);
        $this->addColonne($colonneIdDemandeur);

        $colonneDemandeurInformations = new Column\TextColumn('referent_nom', 'Demandeur');
        $this->addColonne($colonneDemandeurInformations);

        $colonneObjetsInformations = new Column\TextColumn('objetsInformations', 'Objets');
        $colonneObjetsInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneObjetsInformations);

        $colonneDateChoix = new Column\TextColumn('dateChoix', 'Date choix');
        $colonneDateChoix->manipulateRenderCell(
            function($value, \APY\DataGridBundle\Grid\Row $row) {
                return DemandesAbstractGrid::renderCellDateChoix($value, $row);
            }
        )->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDateChoix);

        $colonneEvaluationId = new Column\TextColumn('evaluationEtatId', '');
        $colonneEvaluationId->setFilterable(false)->setSortable(false)->setVisible(false);
        $this->addColonne($colonneEvaluationId);

        $colonneEvaluation = new Column\TextColumn('evaluationEtatLibelle', 'Éval.');
        $colonneEvaluation->setFilterable(false)->setSortable(false);
        $colonneEvaluation->setAlign('center');
        $this->addColonne($colonneEvaluation);

        $colonneEvalDate = new Column\TextColumn('evaluationDateLibelle', '');
        $colonneEvalDate->setFilterable(false)->setVisible(false);
        $this->addColonne($colonneEvalDate);

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
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Évaluer','HopitalNumeriqueInterventionBundle:Admin/Demande:evaluationMass') );
        $this->addMassAction( new Action\ActionMass('Exporter','HopitalNumeriqueInterventionBundle:Admin/Demande:exportMass') );

        $utilisateurConnecte = $this->_container->get('security.context')->getToken()->getUser();

        if ($this->_container->get('nodevo_acl.manager.acl')->checkAuthorization($this->_container->get('router')->generate('hopital_numerique_intervention_admin_demande_delete', array('id' => 0)), $utilisateurConnecte) != -1)
        {
            $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueInterventionBundle:Admin/Demande:gridSupprimeMass') );
        }
    }
}
