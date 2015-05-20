<?php

namespace HopitalNumerique\RechercheBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid ExpBesoinGestion.
 */
class ExpBesoinGestionGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid RechercheParcoursGestion.
     */
    public function setConfig()
    {
        $this->setNoDataMessage('Aucun élément du gestionnaire de recherche par parcours à afficher.');
        $this->setSource( 'hopitalnumerique_recherche.manager.expbesoingestion' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->showIDColumn( false );
        $this->setFilterIdColumn( false );
    }

    /**
     * Ajoute les colonnes du grid RechercheParcoursGestion.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('nom', 'Nom') );
        $this->addColonne( new Column\TextColumn('domaineNom', 'Domaine(s) associé(s)') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_recherche_admin_aide-expression-besoin_gestion_edit' ) );
        $this->addActionButton( new Action\FilsButton('hopital_numerique_expbesoin_index') );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueRechercheBundle:ExpBesoinGestion:deleteMass') );
    }
}