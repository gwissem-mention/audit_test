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
        $this->setNoDataMessage('Aucune intervention à afficher.');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $colonneRegroupements = new Column\TextColumn('nombreRegroupements', '');
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
        
        $this->addColonne(new Column\TextColumn('demandeurInformations', 'Demandeur'));
        $this->addColonne(new Column\TextColumn('interventionInitiateurType', 'Initiateur'));
        $this->addColonne(new Column\TextColumn('ambassadeurInformations', 'Ambassadeur'));
        $this->addColonne(new Column\DateColumn('dateCreationLibelle', 'Création'));
        $this->addColonne(new Column\TextColumn('interventionEtatLibelle', 'État'));
        $this->addColonne(new Column\DateColumn('cmsiDateChoixLibelle', 'Choix CMSI'));
        $this->addColonne(new Column\DateColumn('ambassadeurDateChoixLibelle', 'Choix ambassadeur'));
        $this->addColonne(new Column\TextColumn('evaluationEtatLibelle', 'Évaluation'));
        $this->addColonne(new Column\TextColumn('remboursementEtatLibelle', 'Remboursement'));
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
