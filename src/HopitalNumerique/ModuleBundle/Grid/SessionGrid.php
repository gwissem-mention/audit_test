<?php

namespace HopitalNumerique\ModuleBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Session.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class SessionGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Session.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_module.manager.session' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('- Aucune session planifiée -');
    }

    /**
     * Ajoute les colonnes du grid Session.
     */
    public function setColumns()
    {
        $dateOuvertureInscriptionColumn = new Column\DateColumn('dateOuvertureInscription', 'Date d\'ouverture des inscriptions');
        $dateOuvertureInscriptionColumn->setSize( 150 );
        $this->addColonne( $dateOuvertureInscriptionColumn );
        
        $dateFermetureInscriptionColumn = new Column\DateColumn('dateFermetureInscription', 'Date de clôture des inscriptions');
        $dateFermetureInscriptionColumn->setSize( 150 );
        $this->addColonne( $dateFermetureInscriptionColumn );
        
        $dateSessionColumn = new Column\DateColumn('dateSession', 'Date de début de la session');
        $dateSessionColumn->setSize( 150 );
        $this->addColonne( $dateSessionColumn );
        
        $dureeColumn = new Column\TextColumn('duree', 'Durée de la session');
        $this->addColonne( $dureeColumn );
        
        $horaireColumn = new Column\TextColumn('horaires', 'Horaires');
        $this->addColonne( $horaireColumn );
        
        $nbInscritsColumn = new Column\TextColumn('nbInscrits', 'Nombres d\'inscrits');
        $this->addColonne( $nbInscritsColumn );
        
        $nbInscritsEnAttenteColumn = new Column\TextColumn('nbInscritsEnAttente', 'Nombres d\'inscrits en attente');
        $this->addColonne( $nbInscritsEnAttenteColumn );
        
        $nbInscritsColumn = new Column\TextColumn('placeRestantes', 'Nombres de places restantes');
        $this->addColonne( $nbInscritsColumn );
        
        $etatColumn = new Column\TextColumn('etat', 'Etat');
        $etatColumn->setSize( 140 );
        $etatColumn->setFilterType('select');
        $etatColumn->setOperatorsVisible( false );
        $etatColumn->setDefaultOperator( \APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ );
        $this->addColonne( $etatColumn );
        
        $archiverColumn = new Column\BooleanColumn('archiver', 'Archivée');
        $archiverColumn->setSize( 70 );
        $archiverColumn->setFilterType('select');
        $archiverColumn->setOperatorsVisible( false );
        $archiverColumn->setDefaultOperator( \APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ );
        $this->addColonne( $archiverColumn );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_module_module_session_show' ) );
        
        $actionListeInscrits = new Action\ShowButton('hopitalnumerique_module_module_session_inscription');
        $actionListeInscrits->setAttributes( array(
                'class'=>'btn btn-primary fa fa-users',
                'title' => 'Afficher les inscrits',
        ));
        $this->addActionButton( $actionListeInscrits );

        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_module_module_session_edit' ) );

        //--------- GME 16/06 : Cadenas lock/unlock -------
        //Custom Unlock button : Affiche le bouton déverrouiller si la ligne est verrouillée
        $unlockButton = new Action\LockButton( 'hopitalnumerique_module_module_session_archiver' );
        $unlockButton->setAttributes( array('class'=>'btn btn-warning fa fa-unlock','title' => 'Déverrouiller') );
        $unlockButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row  $row) {
            return $row->getField('archiver') ? $action : null;
        });
        $this->addActionButton( $unlockButton );

        //Custom lock button : Affiche le bouton verrouiller si la ligne est déverrouillée
        $lockButton = new Action\LockButton( 'hopitalnumerique_module_module_session_archiver' );
        $lockButton->setAttributes( array('class'=>'btn btn-warning fa fa-lock','title' => 'Verrouiller') );
        $lockButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row  $row) {
            return $row->getField('archiver') ? null : $action;
        });
        $this->addActionButton( $lockButton );
        //--------- GME 16/06 : Cadenas lock/unlock -------

        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_module_module_session_delete' ) );

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}
