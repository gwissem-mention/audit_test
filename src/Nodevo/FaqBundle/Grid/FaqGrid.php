<?php

namespace Nodevo\FaqBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Faq.
 */
class FaqGrid extends Grid
{
    /**
     * Définie la config spécifique au grid Faq.
     */
    public function setConfig()
    {
        $this->setSource( 'nodevo_faq.manager.faq' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->showIDColumn( false );
        $this->setFilterIdColumn( false );
    }

    /**
     * Ajoute les colonnes du grid Faq.
     */
    public function setColumns()
    {
        $categorieColumn = new Column\TextColumn('categorieName', 'Catégorie');
        $categorieColumn->setSize(100);
        $this->addColonne( $categorieColumn );

        $domaineColumn = new Column\TextColumn('domaineNom', 'Domaine(s)');
        $domaineColumn->setSize( 150 );
        $this->addColonne( $domaineColumn );
        
        $this->addColonne( new Column\TextColumn('question', 'Question') );
        $this->addColonne( new Column\TextColumn('reponse', 'Reponse') );

        /* Colonnes inactives */
        $this->addColonne( new Column\BlankColumn('order') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'nodevo_faq_faq_show' ) );
        $this->addActionButton( new Action\EditButton( 'nodevo_faq_faq_edit' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\DeleteMass('NodevoFaqBundle:Faq:deleteMass') );
    }
}