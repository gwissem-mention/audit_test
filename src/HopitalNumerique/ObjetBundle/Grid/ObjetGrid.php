<?php

namespace HopitalNumerique\ObjetBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Objet.
 */
class ObjetGrid extends Grid implements IGrid
{
    /**
     * Définie la config spécifique au grid Objet.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_objet.manager.objet' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun Objet à afficher.');
        $this->showIDColumn( true );
    }

    /**
     * Ajoute les colonnes du grid Objet.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('titre', 'Titre') );
        $this->addColonne( new Column\TextColumn('types','Types') );
        
        $infraColumn = new Column\BooleanColumn('isInfraDoc', 'Infra-doc');
        $infraColumn->setValues( array( 1 => 'Oui', 0 => 'Non') );
        $infraColumn->setSize( 90 );
        $this->addColonne( $infraColumn );

        $etatColonne = new Column\TextColumn('etat', 'Etat');
        $etatColonne->setSize( 80 );
        $this->addColonne( $etatColonne );

        $this->addColonne( new Column\DateColumn('dateCreation', 'Date de création') );

        $dateColonne = new Column\DateColumn('dateModification', 'Date de dernière mise à jour');
        $dateColonne->setSize( 210 );
        $this->addColonne( $dateColonne );
        
        $this->addColonne( new Column\LockedColumn() );
        $this->addColonne( new Column\BlankColumn('lockedBy') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_objet_objet_show' ) );
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_objet_objet_edit' ) );
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_objet_objet_delete' ) );

        //Custom Unlock button : Affiche le bouton dévérouillé si la ligne est vérouillée
        $unlockButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_objet_objet_cancel');
        $unlockButton->setRouteParameters( array('id', 'message'=>true) );
        $unlockButton->setAttributes( array('class'=>'btn btn-warning fa fa-unlock','title' => 'Dévérouiller') );
        $unlockButton->manipulateRender(function($action, $row) {
            return $row->getField('lock') ? $action : null;
        });
        $this->addActionButton( $unlockButton );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}