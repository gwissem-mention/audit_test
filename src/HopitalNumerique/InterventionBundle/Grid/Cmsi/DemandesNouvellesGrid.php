<?php
/**
 * Configuration du grid des nouvelles demandes d'intervention pour le CMSI.
 */
namespace HopitalNumerique\InterventionBundle\Grid\Cmsi;

use HopitalNumerique\InterventionBundle\Grid\DemandesAbstractGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;

/**
 * Configuration du grid des nouvelles demandes d'intervention pour le CMSI.
 */
class DemandesNouvellesGrid extends DemandesAbstractGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut)
     */
    public function setConfig()
    {
        parent::setConfig();
        $this->setFunctionName('getGridDonneesCmsiDemandesNouvelles');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        parent::setColumns();
        
        $colonneDemandeurInformations = new Column\TextColumn('demandeurInformations', 'Demandeur');
        $colonneDemandeurInformations->manipulateRenderCell(
            function($value, $row, $router)
            {
                return DemandesAbstractGrid::renderCellReferent($value, $row, $router);
            }
        );
        $colonneDemandeurInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDemandeurInformations);
        
        $colonneAmbassadeurInformations = new Column\TextColumn('ambassadeurInformations', 'Ambassadeur');
        $colonneAmbassadeurInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneAmbassadeurInformations);
        
        $colonneObjetsInformations = new Column\TextColumn('objetsInformations', 'Objets');
        $colonneObjetsInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneObjetsInformations);
        
        $colonneInterventionEtatLibelle = new Column\TextColumn('interventionEtatLibelle', 'État');
        $colonneInterventionEtatLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneInterventionEtatLibelle);
        
        $colonneDateCreationLibelle = new Column\DateColumn('dateCreationLibelle', 'Création');
        $colonneDateCreationLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDateCreationLibelle);

        $colonneDateButoir = new Column\TextColumn('dateButoir', 'Date butoir');
        $colonneDateButoir->setAlign('center');
        $colonneDateButoir->setFilterable(false)->setSortable(false);
        $colonneDateButoir->manipulateRenderCell(
            function($value, $row, $router) {
                if ($row->getField('interventionEtatId') == InterventionEtat::getInterventionEtatDemandeInitialeId())
                {
                    $dateCreation = $row->getField('dateCreationLibelle');
                    $dateButoir = new \DateTime($dateCreation);
                    $dateButoir->add(new \DateInterval('P'.InterventionEtat::$VALIDATION_CMSI_NOMBRE_JOURS.'D'));
                    return $dateButoir->format('d/m/Y');
                }
                else if ($row->getField('interventionEtatId') == InterventionEtat::getInterventionEtatAttenteCmsiId())
                {
                    return 'En attente';
                }
                return '';
            }
        );
        $this->addColonne($colonneDateButoir);
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new Action\ShowButton('hopital_numerique_intervention_demande_voir'));
        $this->addActionButton(new Action\EditButton('hopital_numerique_intervention_demande_edit'));
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
