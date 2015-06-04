<?php

namespace HopitalNumerique\ExpertBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid ActiviteExpert.
 */
class ActiviteExpertGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid ActiviteExpert.
     */
    public function setConfig()
    {
        $this->setSource( 'HopitalNumeriqueExpertBundle:ActiviteExpert' );
        $this->setNoDataMessage('Aucun activiteexpert à afficher.');
    }

    /**
     * Ajoute les colonnes du grid ActiviteExpert.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('id', 'Id') );
        $this->addColonne( new Column\TextColumn('titre', 'Titre') );
        $this->addColonne( new Column\TextColumn('dateDebut', 'Datedebut') );
        $this->addColonne( new Column\TextColumn('dateFin', 'Datefin') );
        $this->addColonne( new Column\TextColumn('nbVacationParExpert', 'Nbvacationparexpert') );
        $this->addColonne( new Column\TextColumn('etatValidation', 'Etatvalidation') );        
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_expert_expert_activite_show' ) );
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_expert_expert_activite_edit' ) );
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_expert_expert_activite_delete' ) );

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}