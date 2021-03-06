<?php

namespace HopitalNumerique\DomaineBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Domaine.
 */
class DomaineGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Domaine.
     */
    public function setConfig()
    {
        $this->setNoDataMessage('Aucun domaine à afficher.');
        $this->setSource('hopitalnumerique_domaine.manager.domaine');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->showIDColumn(false);
        $this->setFilterIdColumn(false);
    }

    /**
     * Ajoute les colonnes du grid Domaine.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne(new Column\NumberColumn('idDomaine', 'ID'));
        $this->addColonne(new Column\TextColumn('nom', 'Nom'));
        $this->addColonne(new Column\TextColumn('url', 'Url'));
        $this->addColonne(new Column\TextColumn('adresseMailContact', 'Adresse mail de contact'));
        $this->addColonne(new Column\TextColumn('templateNom', 'Template'));
    }

    /**
     * Ajoute les boutons d'action.
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new Action\EditButton('hopitalnumerique_domaine_admin_domaine_edit'));
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction(new Action\DeleteMass('HopitalNumeriqueDomaineBundle:DomaineMass:deleteMass'));
    }
}
