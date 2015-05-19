<?php

namespace HopitalNumerique\RechercheParcoursBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid RechercheParcoursGestion.
 */
class RechercheParcoursGestionGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid RechercheParcoursGestion.
     */
    public function setConfig()
    {
        $this->setNoDataMessage('Aucun élément du gestionnaire de recherche par parcours à afficher.');
        $this->setSource( 'hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion' );
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
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion_edit' ) );
        $this->addActionButton( new Action\FilsButton('hopital_numerique_recherche_parcours_homepage') );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion:deleteMass') );
        
    }
}