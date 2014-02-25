<?php

namespace Nodevo\MailBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Mail.
 */
class MailGrid extends Grid implements IGrid
{
    private $_allowDelete;

    /**
    * Constructeur du grid, on lui passe le conteneur de services, un booléen si la suppression des mails est autorisée
    *
    * @param container Le conteneur de services
    * @param allowDelete La suppression de mail est-elle autorisée ?
    */
    public function __construct($container, $allowDelete = true)
    {
        parent::__construct( $container );

        $this->_allowDelete = $allowDelete;
    }

    /**
     * Définie la config spécifique au grid Mail.
     */
    public function setConfig()
    {
        $this->setSource( 'NodevoMailBundle:Mail' );
        $this->setNoDataMessage('Aucun E-Mail à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Mail.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('objet', 'Objet') );
        $this->addColonne( new Column\TextColumn('description', 'Description') );


        $this->addColonne( new Column\BlankColumn('expediteurMail') );
        $this->addColonne( new Column\BlankColumn('expediteurName') );
        $this->addColonne( new Column\BlankColumn('body') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton('nodevo_mail_mail_edit') );
        $this->addActionButton( new Action\ShowButton('nodevo_mail_mail_show') );

        if( $this->_allowDelete )
            $this->addActionButton( new Action\DeleteButton('nodevo_mail_mail_delete') );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        
    }
}