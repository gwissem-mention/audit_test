<?php

namespace HopitalNumerique\ReportBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Report.
 */
class ReportGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Report.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_report.manager.report' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun signalement de bug à afficher.');
        $this->setDefaultOrder('date', 'ASC');
    }

    /**
     * Ajoute les colonnes du grid Report.
     */
    public function setColumns()
    {
        //field, titre, isSortable, size
        $this->addColonne( new Column\TextColumn('date', 'Date / heure') );
        $this->addColonne( new Column\TextColumn('nomPrenom', 'Nom / prénom') );   
        $this->addColonne( new Column\TextColumn('userMail', 'Mail rapporteur') );   
        $this->addColonne( new Column\TextColumn('url', 'Url') );
        $this->addColonne( new Column\TextColumn('userAgent', 'User Agent') );
        $this->addColonne( new Column\TextColumn('observations', 'Observations') );

        $userIdColumn = new Column\TextColumn('userId', 'userId');
        $userIdColumn->setVisible(false);
        $userIdColumn->setFilterable(false);
        $this->addColonne( $userIdColumn );

        $archiverColumn = new Column\BooleanColumn('archive', 'Archivé');
        $archiverColumn->setSize( 70 );
        $archiverColumn->setFilterType('select');
        $archiverColumn->setOperatorsVisible( false );
        $this->addColonne( $archiverColumn );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopitalnumerique_report_admin_report_show' ) );

        //Custom Unlock button : Affiche le bouton 'Archiver' si la ligne n'est pas archivée
        $lockButton = new Action\LockButton( 'hopitalnumerique_report_archiver' );
        $lockButton->setAttributes( array('class'=>'btn btn-warning fa fa-unlock','title' => 'Archiver') );
        $lockButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row  $row) {
            return $row->getField('archive') ?  null : $action;
        });
        $this->addActionButton( $lockButton );

        //Custom Unlock button : Affiche le bouton 'Désarchiver' si la ligne est archivée
        $unlockButton = new Action\LockButton( 'hopitalnumerique_report_archiver' );
        $unlockButton->setAttributes( array('class'=>'btn btn-warning fa fa-lock','title' => 'Désarchiver') );
        $unlockButton->manipulateRender(function($action, \APY\DataGridBundle\Grid\Row  $row) {
            return $row->getField('archive') ? $action : null;
        });
        $this->addActionButton( $unlockButton );

        $actionFicheUtilisateur = new Action\ShowButton('hopital_numerique_user_show');
        $actionFicheUtilisateur->setAttributes( array(
                'class'=>'btn btn-primary fa fa-user-md',
                'title' => 'Fiche de l\'utilisateur',
                'target' => '_blank'
        ));
        $actionFicheUtilisateur->setRouteParametersMapping(array('userId' => 'id'));
        $actionFicheUtilisateur->setRouteParameters(array('userId'));
        $this->addActionButton( $actionFicheUtilisateur );

    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        
        
    }
}