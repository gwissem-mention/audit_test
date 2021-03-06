<?php

namespace HopitalNumerique\ModuleBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;
use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * Configuration du grid Module.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ModuleGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Module.
     */
    public function setConfig()
    {
        //Manager
        $this->setSource('hopitalnumerique_module.manager.module');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);

        $this->setNoDataMessage('Aucun module à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Module.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne(new Column\TextColumn('titre', 'Titre'));

        $domaineColumn = new Column\TextColumn('domaineNom', 'Domaine(s) associé(s)');
        $this->addColonne($domaineColumn);

        $formateurColumn = new Column\TextColumn('formateur', 'Animateur');
        $formateurColumn->setSize(400);
        $this->addColonne($formateurColumn);

        $statutColumn = new Column\TextColumn('statut', 'Statut');
        $statutColumn->setSize(80);
        $statutColumn->setFilterType('select');
        $statutColumn->setSelectFrom('source');
        $statutColumn->setOperatorsVisible(false);
        $statutColumn->setDefaultOperator(\APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ);
        $this->addColonne($statutColumn);

        $productionColumn = new Column\TextColumn('prod_titre', 'Productions concernées');
        $productionColumn->setFilterable(false);
        $productionColumn->setVisible(false);
        $this->addColonne($productionColumn);
    }

    /**
     * Ajoute les boutons d'action.
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new Action\ShowButton('hopitalnumerique_module_module_show'));
        $this->addActionButton(new Action\FilsButton('hopitalnumerique_module_module_session'));
        $this->addActionButton(new Action\EditButton('hopitalnumerique_module_module_edit'));

        //Boutton d'ajout d'un référentiel avec le même code par défaut
        $ajoutSession = new RowAction('', 'hopitalnumerique_module_module_session_add');
        $ajoutSession->setRouteParameters(['id']);
        $ajoutSession->setAttributes(['class' => 'btn btn-warning fa fa-plus', 'title' => 'Ajouter une session']);
        $this->addActionButton($ajoutSession);

        // $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_module_module_delete' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction(new Action\ActionMass('Supprimer', 'HopitalNumeriqueModuleBundle:Back/Module:deleteMass'));
    }
}
