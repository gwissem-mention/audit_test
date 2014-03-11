<?php
/**
 * Configuration du grid des demandes d'intervention.
 */
namespace HopitalNumerique\InterventionBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid des demandes d'intervention.
 */
class DemandesNouvellesGrid extends Grid implements IGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_intervention.manager.intervention_demande');
        $this->setFunctionName('getGridDonnees_DemandesNouvelles');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('Aucune intervention à afficher.');
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $this->addColonne(new Column\TextColumn('demandeurInformations', 'Demandeur'));
        $this->addColonne(new Column\TextColumn('ambassadeurInformations', 'Ambassadeur'));
        $this->addColonne(new Column\TextColumn('objetsInformations', 'Objets'));
        $this->addColonne(new Column\TextColumn('interventionEtatLibelle', 'État'));
        $this->addColonne(new Column\DateColumn('dateCreationLibelle', 'Date de création'));
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        /*$this->addActionButton(new Action\EditButton('hopital_numerique_user_edit'));
        $this->addActionButton(new Action\ShowButton('hopital_numerique_user_show'));
        $this->addActionButton(new Action\DeleteButton('hopital_numerique_user_delete'));*/
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
