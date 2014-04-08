<?php

namespace Nodevo\FaqBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Faq.
 */
class FaqGrid extends Grid implements IGrid
{
    /**
     * Définie la config spécifique au grid Faq.
     */
    public function setConfig()
    {
        // $this->setSource( 'nodevo_faq.manager.faq' );
        $this->setSourceType( self::SOURCE_TYPE_ENTITY );
        $this->setSource( 'NodevoFaqBundle:Faq' );
        $this->setNoDataMessage('Aucun élément de la FAQ à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Faq.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('question', 'Question') );
        $this->addColonne( new Column\TextColumn('reponse', 'Reponse') );
        $this->addColonne( new Column\TextColumn('categorie.name', 'Catégorie') );

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