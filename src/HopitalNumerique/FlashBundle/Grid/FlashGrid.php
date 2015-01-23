<?php

namespace HopitalNumerique\FlashBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Flash.
 */
class FlashGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Flash.
     */
    public function setConfig()
    {
        $this->setSource( 'HopitalNumeriqueFlashBundle:Flash' );
        $this->setNoDataMessage('Aucun flash message à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Flash.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('title', 'Titre') );
        $this->addColonne( new Column\TextColumn('content', 'Contenu') );

        $publieColumn = new Column\BooleanColumn('isPublished', 'Publié ?');
        $publieColumn->setSize( 90 );
        $this->addColonne( $publieColumn );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_flash_flash_edit' ) );
        // $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_flash_flash_delete' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueFlashBundle:Flash:deleteMass') );
    }
}
