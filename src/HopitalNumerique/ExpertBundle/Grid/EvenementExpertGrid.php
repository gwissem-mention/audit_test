<?php

namespace HopitalNumerique\ExpertBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid EvenementExpert.
 */
class EvenementExpertGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid EvenementExpert.
     */
    public function setConfig()
    {
        $this->setSource( 'HopitalNumeriqueExpertBundle:EvenementExpert' );
        $this->setNoDataMessage('Aucun evenementexpert à afficher.');
    }

    /**
     * Ajoute les colonnes du grid EvenementExpert.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size 
        $this->addColonne( new Column\AssocColumn('nom.libelle', 'Nom') );  
        $this->addColonne( (new Column\DateColumn('date', 'Date', 'd/m/Y'))->setSize(250) );
        $this->addColonne( (new Column\TextColumn('nbVacation', 'Nombre de vacation'))->setSize(120) );     
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_expert_evenement_expert_edit' ) );
        $this->addActionButton( new Action\FancyButton( 'hopitalnumerique_expert_expert_expert_parametrage' ) );
        $this->addActionButton( new Action\DownloadButton( 'hopitalnumerique_expert_evenement_expert_impression_fiche' ) );

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}