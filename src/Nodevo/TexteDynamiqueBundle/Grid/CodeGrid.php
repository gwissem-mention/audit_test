<?php

namespace Nodevo\TexteDynamiqueBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Code.
 */
class CodeGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Code.
     */
    public function setConfig()
    {
        //Manager
        $this->setSource( 'nodevo_textedynamique.manager.code' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        
        $this->setNoDataMessage('Aucun code à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Code.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('code', 'Code') );
        
        $domaineColumn = new Column\TextColumn('domaineNom', 'Domaine(s) associé(s)');
        $this->addColonne( $domaineColumn );

        $this->addColonne( new Column\TextColumn('texte', 'Texte') );        
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton( 'nodevo_textedynamique_code_edit' ) );

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        //TODO : faire action de masse pour le delete
        
    }
}