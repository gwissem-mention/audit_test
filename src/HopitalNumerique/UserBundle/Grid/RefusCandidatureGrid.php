<?php

namespace HopitalNumerique\UserBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid RefusCandidature.
 */
class RefusCandidatureGrid extends Grid implements IGrid
{
    /**
     * Définie la config spécifique au grid RefusCandidature.
     */
    public function setConfig()
    {
        $this->setSource( 'HopitalNumeriqueUserBundle:RefusCandidature' );
        $this->setNoDataMessage('Aucun RefusCandidature à afficher.');
    }

    /**
     * Ajoute les colonnes du grid RefusCandidature.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('user.nom', 'Utilisateur') ); 
        $this->addColonne( new Column\TextColumn('motifRefus', 'Motif du refus') );
        $this->addColonne( new Column\DateColumn('dateRefus', 'Date de refus') ); 
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_user_refuscandidature_show' ) );

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}