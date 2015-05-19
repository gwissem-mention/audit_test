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
        $this->setSource( 'HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion' );
        $this->setNoDataMessage('Aucun rechercheparcoursgestion à afficher.');
    }

    /**
     * Ajoute les colonnes du grid RechercheParcoursGestion.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('id', 'Id') );
        $this->addColonne( new Column\TextColumn('nom', 'Nom') );        
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion_show' ) );
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion_edit' ) );
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion_delete' ) );

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}