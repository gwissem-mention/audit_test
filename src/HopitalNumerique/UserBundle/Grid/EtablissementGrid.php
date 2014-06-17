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
        $this->setSource( 'hopitalnumerique_user.manager.user' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setFunctionName('getEtablissementForGrid');
        $this->setNoDataMessage('Aucun établissement "autre" référencé.');
        $this->setButtonSize(43);
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('username', 'Identifiant (login)') );
        $this->addColonne( new Column\TextColumn('nom', 'Nom') );
        $this->addColonne( new Column\TextColumn('prenom', 'Prénom') );
        
        $regionColonne = new Column\TextColumn('region', 'Région');
        $regionColonne->setSize( 150 );
        $regionColonne->setFilterType( 'select' );
        $regionColonne->setSelectFrom( 'source' );
        $regionColonne->setOperatorsVisible( false );
        $this->addColonne( $regionColonne );

        $this->addColonne( new Column\TextColumn('autreStructureRattachementSante', 'Etablissement autre') );

        $archiverColonne = new Column\BooleanColumn('archiver', 'Archivé');
        $archiverColonne->setSize(100);
        $this->addColonne( $archiverColonne );
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
        $this->addMassAction( new Action\ActionMass('Export CSV', 'HopitalNumeriqueEtablissementBundle:Etablissement:exportCsvAutres') );
    }
}
