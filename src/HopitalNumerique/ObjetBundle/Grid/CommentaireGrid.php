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
        $this->setSource( 'HopitalNumeriqueObjetBundle:Commentaire' );
        $this->setNoDataMessage('Aucun Commentaire à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Commentaire.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('id', 'Id') );
        $this->addColonne( new Column\TextColumn('dateCreation', 'Datecreation') );
        $this->addColonne( new Column\TextColumn('texte', 'Texte') );
        $this->addColonne( new Column\TextColumn('publier', 'Publier') );        
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_objet_admin_commentaire_edit' ) );
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_objet_admin_commentaire_delete' ) );

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}