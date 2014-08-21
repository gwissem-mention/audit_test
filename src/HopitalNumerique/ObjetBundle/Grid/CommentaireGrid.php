<?php

namespace HopitalNumerique\ObjetBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Commentaire.
 */
class CommentaireGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Commentaire.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_objet.manager.commentaire' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun commentaire à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Commentaire.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('nomPrenom', 'Utilisateur') );
        $this->addColonne( new Column\TextColumn('texte', 'Texte') );
        $this->addColonne( new Column\DateColumn('dateCreation', 'Date du commentaire') );
        $this->addColonne( new Column\TextColumn('objId', 'Objet Id') );
        $this->addColonne( new Column\TextColumn('objTitre', 'Objet concerné') );
        $this->addColonne( new Column\TextColumn('contId', 'Infradoc Id') );
        $this->addColonne( new Column\TextColumn('contTitre', 'Infradoc concerné') );
        $this->addColonne( new Column\BooleanColumn('publier', 'Publier') );        
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_objet_admin_commentaire_edit' ) );
        //--------- GME 14/08 : Cadenas lock/unlock -------
        //Custom Unlock button : Affiche le bouton dévérouillé si la ligne est vérouillée
        $unlockButton = new Action\LockButton( 'hopitalnumerique_objet_admin_commentaire_toggle_publication' );
        $unlockButton->setAttributes( array('class'=>'btn btn-warning fa fa-unlock','title' => 'Publier') );
        $unlockButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row  $row) {
            return $row->getField('publier') ? null : $action;
        });
        $this->addActionButton( $unlockButton );

        //Custom Unlock button : Affiche le bouton dévérouillé si la ligne est vérouillée
        $lockButton = new Action\LockButton( 'hopitalnumerique_objet_admin_commentaire_toggle_publication' );
        $lockButton->setAttributes( array('class'=>'btn btn-warning fa fa-lock','title' => 'Dépublier') );
        $lockButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row  $row) {
            return $row->getField('publier') ? $action : null ;
        });
        $this->addActionButton( $lockButton );
        //--------- GME 14/08 : Cadenas lock/unlock -------
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_objet_admin_commentaire_delete' ) );

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Export CSV'  ,'HopitalNumeriqueObjetBundle:MassCommentaire:exportCSV') );
        $this->addMassAction( new Action\ActionMass('Publication - Publier'  ,'HopitalNumeriqueObjetBundle:MassCommentaire:publierMass') );
        $this->addMassAction( new Action\ActionMass('Publication - Dépublier'  ,'HopitalNumeriqueObjetBundle:MassCommentaire:depublierMass') );
        $this->addMassAction( new Action\ActionMass('Suppression'  ,'HopitalNumeriqueObjetBundle:MassCommentaire:deleteMass') );
    }
}