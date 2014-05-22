<?php

namespace Nodevo\FaqBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
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
        $this->setSource( 'NodevoFaqBundle:Faq' );
        $this->setNoDataMessage('Aucun élément de la FAQ à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Faq.
     */
    public function setColumns()
    {
        $categorieColumn = new Column\AssocColumn('categorie.name', 'Catégorie');
        $categorieColumn->setSize(100);
        $this->addColonne( $categorieColumn );
        
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
        $this->addActionButton( new Action\DeleteButton( 'nodevo_faq_faq_delete' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
    }
}