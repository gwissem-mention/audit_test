<?php

namespace HopitalNumerique\UserBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Contractualisation.
 */
class ContractualisationGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Contractualisation.
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_user.manager.contractualisation');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setFunctionName('getContractualisationForGrid');
        $this->setNoDataMessage('Aucun fichier de contractualisation à afficher.');
        $this->setButtonSize(43);
    }

    /**
     * Ajoute les colonnes du grid Contractualisation.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne(new Column\TextColumn('nomDocument', 'Nom document'));

        $archiverColonne = new Column\BooleanColumn('archiver', 'Document archivé');
        $archiverColonne->setSize(150);
        $this->addColonne($archiverColonne);

        $colonneDate = new Column\DateColumn('dateRenouvellement', 'Date renouvellement', 'd/m/Y H:i:s');
        $colonneDate->setSize(170);
        $this->addColonne($colonneDate);

        $typeDocumentColonne = new Column\TextColumn('libelle', 'Type de document');
        $this->addColonne($typeDocumentColonne);

        /* Colonnes inactives */
        $this->addColonne(new Column\BlankColumn('path'));
    }

    /**
     * Ajoute les boutons d'action.
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new Action\DownloadButton('hopitalnumerique_user_contractualisation_dowload'));
        $this->addActionButton(new Action\ShowButton('hopitalnumerique_user_contractualisation_show'));
        $this->addActionButton(new Action\EditButton('hopitalnumerique_user_contractualisation_edit'));

        //Custom Archive button : Affiche le bouton archiver
        $archiveButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_user_contractualisation_archiver');
        $archiveButton->setRouteParameters(['id']);
        $archiveButton->manipulateRender(function (\APY\DataGridBundle\Grid\Action\RowAction $action, \APY\DataGridBundle\Grid\Row $row) {
            if ($row->getField('archiver')) {
                $action->setAttributes(['class' => 'btn btn-warning fa fa-archive', 'title' => 'Archiver']);
            } else {
                $action->setAttributes(['class' => 'btn btn-warning fa fa-folder-open', 'title' => 'Désarchiver']);
            }

            return $action;
        });
        $this->addActionButton($archiveButton);

        $this->addActionButton(new Action\DeleteButton('hopitalnumerique_user_contractualisation_delete'));
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
    }
}
