<?php

namespace HopitalNumerique\ObjetBundle\Grid;

use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Row;
use Nodevo\GridBundle\Grid\Action\ActionMass;
use Nodevo\GridBundle\Grid\Action\EditButton;
use Nodevo\GridBundle\Grid\Action\ShowButton;
use Nodevo\GridBundle\Grid\Column\BlankColumn;
use Nodevo\GridBundle\Grid\Column\BooleanColumn;
use Nodevo\GridBundle\Grid\Column\DateColumn;
use Nodevo\GridBundle\Grid\Column\LockedColumn;
use Nodevo\GridBundle\Grid\Column\NumberColumn;
use Nodevo\GridBundle\Grid\Column\TextColumn;
use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;

/**
 * Configuration du grid Objet.
 */
class ObjetGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Objet.
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_objet.manager.objet');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('Aucun Objet à afficher.');
        $this->showIDColumn(false);
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->_grid->setId($id);
    }

    /**
     * @param string $filtre
     */
    public function setDefaultFiltreFromController($filtre)
    {
        $filtres = [];

        switch ($filtre) {
            case 'point-dur':
                $filtres['types'] = 'Point dur';
                break;
            case 'Article':
                $filtres['isArticle'] = true;
                break;
            case 'publication':
                $filtres['isArticle'] = false;
                break;
            case 'production':
                $filtres['types'] = 'Article';
                break;
            case 'non-publie':
                $filtres['etat'] = 'Inactif';
                break;
        }

        if (!empty($filtres)) {
            $this->setDefaultFilters($filtres);
        }

        $this->setPersistence(true);
    }

    /**
     * Ajoute les colonnes du grid Objet.
     */
    public function setColumns()
    {
        $this->addColonne(new NumberColumn('idReference', 'ID'));
        $this->addColonne(new TextColumn('titre', 'Titre'));
        $this->addColonne(new TextColumn('types', 'Catégories'));
        $this->addColonne(new TextColumn('domainesNom', 'Domaine(s) associé(s)'));

        $infraColumn = new BooleanColumn('isInfraDoc', 'Infra-doc ?');
        $infraColumn->setSize(90);
        $this->addColonne($infraColumn);

        $isArticleColumn = new BooleanColumn('isArticle', 'Article ?');
        $isArticleColumn->setVisible(false);
        $isArticleColumn->setFilterable(false);
        $this->addColonne($isArticleColumn);

        $etatColonne = new TextColumn('etat', 'Etat');
        $etatColonne->setSize(80);
        $etatColonne->setFilterType('select');
        $etatColonne->setSelectFrom('source');
        $etatColonne->setOperatorsVisible(false);
        $etatColonne->setDefaultOperator(Column::OPERATOR_EQ);
        $this->addColonne($etatColonne);

        $this->addColonne(new DateColumn('dateCreation', 'Date de création'));

        $this->addColonne(new NumberColumn('nbVue', 'Nombre de vues'));

        $this->addColonne(new NumberColumn('moyenne', 'Note moyenne'));

        $this->addColonne(new NumberColumn('nbNotes', 'Nombre de notes'));

        $this->addColonne(new LockedColumn());

        /* Colonnes inactives */
        $this->addColonne(new BlankColumn('lockedBy'));
        $this->addColonne(new BlankColumn('dateModification'));
    }

    /**
     * Ajoute les boutons d'action.
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new ShowButton('hopitalnumerique_objet_objet_show'));
        $this->addActionButton(new EditButton('hopitalnumerique_objet_objet_edit'));

        //Goto Refs
        $referencesButton = new RowAction('', 'hopitalnumerique_objet_objet_edit');
        $referencesButton->setRouteParameters(['id', 'infra' => 0, 'toRef' => 1]);
        $referencesButton->setAttributes(
            ['class' => 'btn btn-primary fa fa-cog', 'title' => 'Accès direct aux références']
        );
        $referencesButton->manipulateRender(
            function ($action, Row $row) {
                return !$row->getField('isArticle') ? $action : null;
            }
        );
        $this->addActionButton($referencesButton);

        $filtre = $this->_defaultFilters;
        if ('isArticle' == key($filtre)) {
            if (reset($filtre)) {
                $filtre[key($filtre)] = 'Article';
            } else {
                $filtre[key($filtre)] = 'publication';
            }
        }
        // Custom Unlock button : Affiche le bouton dévérouillé si la ligne est vérouillée
        if (null != $filtre && null != reset($filtre)) {
            $unlockButton = new RowAction(
                '',
                'hopitalnumerique_objet_objet_cancel_with_filtre'
            );
            $unlockButton->setRouteParameters(
                [
                    'id',
                    'message' => true,
                    'filtre' => reset($filtre),
                ]
            );
        } else {
            $unlockButton = new RowAction('', 'hopitalnumerique_objet_objet_cancel');
            $unlockButton->setRouteParameters(
                [
                    'id',
                    'message' => true,
                ]
            );
        }
        $unlockButton->setAttributes(
            [
                'class' => 'btn btn-warning fa fa-unlock',
                'title' => 'Déverrouiller',
            ]
        );
        $unlockButton->manipulateRender(
            function ($action, Row $row) {
                return $row->getField('lock') ? $action : null;
            }
        );
        $this->addActionButton($unlockButton);
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction(new ActionMass('Export CSV', 'HopitalNumeriqueObjetBundle:Export:exportCsv'));
        $this->addMassAction(new ActionMass('Exporter les rapports', 'HopitalNumeriqueObjetBundle:Objet:exportReport'));
        $this->addMassAction(new ActionMass('Supprimer', 'HopitalNumeriqueObjetBundle:Objet:deleteMass'));
    }
}
