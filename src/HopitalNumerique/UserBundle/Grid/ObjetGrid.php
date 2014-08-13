<?php

namespace HopitalNumerique\UserBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Objet.
 */
class ObjetGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Objet.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_objet.manager.objet' );
        $this->setFunctionName('getDatasForGridAmbassadeur');
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun Objet maîtrisés à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Objet.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('titre', 'Titre') );
        $this->addColonne( new Column\TextColumn('types', 'Type de production') );

        $userColumn = new Column\BlankColumn('user');
        $userColumn->setVisibleForSource(true);
        $this->addColonne( $userColumn );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_objet_objet_show' ) );
        
        $deleteButton = new Action\DeleteButton( 'hopitalnumerique_user_ambassadeur_deleteobjet' );
        $deleteButton->setRouteParameters( array('id', 'user') );
        $this->addActionButton( $deleteButton );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}