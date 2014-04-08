<?php 
namespace HopitalNumerique\UserBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du Grid User
 */
class EtablissementGrid extends Grid implements IGrid
{
    /**
    * Set la config propre au Grid User (Source + config par défaut)
    */
    public function setConfig()
    {
        $this->setSource( 'HopitalNumeriqueUserBundle:User' );
        $this->setNoDataMessage('Aucun établissement "autre" référencé.');
        $this->setButtonSize(43);
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('username', 'Nom du compte') );
        $this->addColonne( new Column\TextColumn('nom', 'Nom') );
        $this->addColonne( new Column\TextColumn('prenom', 'Prénom') );
        
        $regionColonne = new Column\AssocColumn('region.libelle', 'Région');
        $regionColonne->setSize( 150 );
        $this->addColonne( $regionColonne );

        $this->addColonne( new Column\TextColumn('autreStructureRattachementSante', 'Etablissement autre') );

        $archiverColonne = new Column\BooleanColumn('archiver', 'Archivé');
        $archiverColonne->setSize(100);
        $this->addColonne( $archiverColonne );

        /* Colonnes inactives */
        $this->addColonne( new Column\BlankColumn('email') );
        $this->addColonne( new Column\BlankColumn('enabled') );
        $this->addColonne( new Column\BlankColumn('password') );
        $this->addColonne( new Column\BlankColumn('confirmationToken') );
        $this->addColonne( new Column\BlankColumn('locked') );
        $this->addColonne( new Column\BlankColumn('expiresAt') );
        $this->addColonne( new Column\BlankColumn('credentialsExpired') );
        $this->addColonne( new Column\BlankColumn('dateInscription') );
        $this->addColonne( new Column\BlankColumn('telephonePortable') );
        $this->addColonne( new Column\BlankColumn('nomStructure') );
        $this->addColonne( new Column\BlankColumn('lock') );
        $this->addColonne( new Column\BlankColumn('usernameCanonical') );
        $this->addColonne( new Column\BlankColumn('emailCanonical') );
        $this->addColonne( new Column\BlankColumn('salt') );
        $this->addColonne( new Column\BlankColumn('lastLogin') );
        $this->addColonne( new Column\BlankColumn('passwordRequestedAt') );
        $this->addColonne( new Column\BlankColumn('expired') );
        $this->addColonne( new Column\BlankColumn('roles') );
        $this->addColonne( new Column\BlankColumn('credentialsExpireAt') );
        $this->addColonne( new Column\BlankColumn('telephoneDirect') );
        $this->addColonne( new Column\BlankColumn('contactAutre') );
        $this->addColonne( new Column\BlankColumn('fonctionDansEtablissementSante') );
        $this->addColonne( new Column\BlankColumn('fonctionStructure') );
        $this->addColonne( new Column\BlankColumn('credentialsExpireAt') );
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\ShowButton( 'hopital_numerique_user_show' ) );
        $this->addActionButton( new Action\EditButton( 'hopital_numerique_user_edit' ) );

        //Custom Archive button : Affiche le bouton archiver
        $archiveButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_etablissement_archiver');
        $archiveButton->setRouteParameters( array('id') );
        $archiveButton->manipulateRender(function($action, $row) {
            if( !$row->getField( 'archiver') )
                $action->setAttributes( array('class'=>'btn btn-warning fa fa-archive', 'title' => 'Archiver') );
            else
                $action->setAttributes( array('class'=>'btn btn-warning fa fa-folder-open', 'title' => 'Désarchiver') );

            return $action;
        });
        $this->addActionButton( $archiveButton );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        
    }
}
