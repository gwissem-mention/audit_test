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
        $this->setSource( 'hopitalnumerique_expert.manager.evenementexpert' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun evenementexpert à afficher.');
        $this->setButtonSize(49);
    }

    /**
     * Ajoute les colonnes du grid EvenementExpert.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size 
        $this->addColonne( new Column\TextColumn('nom', 'Nom') );  
        $this->addColonne( (new Column\DateColumn('date', 'Date', 'd/m/Y'))->setSize(250) );
        $this->addColonne( (new Column\TextColumn('nbVacation', 'Nombre de vacation'))->setSize(120) );     
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_expert_evenement_expert_edit' ) );

        $fancyButton = new Action\FancyButton( 'hopitalnumerique_expert_expert_expert_parametrage' );
        $fancyButton->setAttributes(array('class'=>'btn btn-primary fancy fancybox.ajax fa fa-users','title' => 'Attester les présences'));
        $this->addActionButton($fancyButton);

        $downloadButton = new Action\DownloadButton( 'hopitalnumerique_expert_evenement_expert_impression_fiche' );
        $downloadButton->setAttributes(array('class'=>'btn btn-warning fa fa-download','title' => 'Télécharger la feuille d\'émargement'));
        $this->addActionButton($downloadButton);

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}