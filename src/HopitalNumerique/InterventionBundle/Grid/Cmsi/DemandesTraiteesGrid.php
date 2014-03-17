<?php
/**
 * Configuration du grid des demandes d'intervention traitées pour le CMSI.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Cmsi;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid des demandes d'intervention traitées pour le CMSI.
 */
class DemandesTraiteesGrid extends Grid implements IGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_intervention.manager.intervention_demande');
        $this->setFunctionName('getGridDonnees_CmsiDemandesTraitees');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('- Aucune intervention à afficher -');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $colonneRegroupements = new Column\TextColumn('nombreRegroupements', '');
        $colonneRegroupements->setFilterable(false)->setSortable(false);
        $colonneRegroupements->manipulateRenderCell(
            function($value, $row, $router) {
                if (intval($row->getField('nombreRegroupements')) > 0)
                {
                    return '<img src="/bundles/hopitalnumeriquecore/img/common-sprite/users.jpg" width="16" height="14">';
                }
                return '';
            }
        );
        $this->addColonne($colonneRegroupements);
        
        $colonneDemandeurInformations = new Column\TextColumn('demandeurInformations', 'Demandeur');
        $colonneDemandeurInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDemandeurInformations);
        
        $colonneInterventionInitiateurType = new Column\TextColumn('interventionInitiateurType', 'Initiateur');
        $colonneInterventionInitiateurType->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneInterventionInitiateurType);
        
        $colonneAmbassadeurInformations = new Column\TextColumn('ambassadeurInformations', 'Ambassadeur');
        $colonneAmbassadeurInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneAmbassadeurInformations);
        
        $colonneDateCreationLibelle = new Column\TextColumn('dateCreationLibelle', 'Création');
        $colonneDateCreationLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDateCreationLibelle);
        
        $colonneInterventionEtatLibelle = new Column\TextColumn('interventionEtatLibelle', 'État');
        $colonneInterventionEtatLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneInterventionEtatLibelle);
        
        $colonneCmsiDateChoixLibelle = new Column\TextColumn('cmsiDateChoixLibelle', 'CMSI');
        $colonneCmsiDateChoixLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneCmsiDateChoixLibelle);
        
        $colonneAmbassadeurDateChoixLibelle = new Column\TextColumn('ambassadeurDateChoixLibelle', 'Ambassadeur');
        $colonneAmbassadeurDateChoixLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneAmbassadeurDateChoixLibelle);
        
        $colonneEvaluationEtatLibelle = new Column\TextColumn('evaluationEtatLibelle', 'Éval.');
        $colonneEvaluationEtatLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneEvaluationEtatLibelle);

        //$this->addColonne(new Column\TextColumn('remboursementEtatLibelle', 'Remboursement'));
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
        /*$this->addMassAction(new Action\DeleteMass('HopitalNumeriqueUserBundle:User:DeleteMass'));
        $this->addMassAction(new Action\Export\CsvMass('HopitalNumeriqueUserBundle:User:ExportCsv'));

        $this->addMassAction(new Action\ActionMass('Activer', 'HopitalNumeriqueUserBundle:User:ActiverMass'));
        $this->addMassAction(new Action\ActionMass('Désactiver', 'HopitalNumeriqueUserBundle:User:DesactiverMass'));*/
    }
}
