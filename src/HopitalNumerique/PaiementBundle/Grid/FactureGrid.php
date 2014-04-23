<?php

namespace HopitalNumerique\PaiementBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Facture.
 */
class FactureGrid extends Grid implements IGrid
{
    /**
     * Définie la config spécifique au grid Facture.
     */
    public function setConfig()
    {
        $this->setSource( 'HopitalNumeriquePaiementBundle:Facture' );
        $this->setNoDataMessage('Aucun Facture à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Facture.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('dateCreation', 'Datecreation') );        
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_paiement_facture_show' ) );
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_paiement_facture_edit' ) );
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_paiement_facture_delete' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}