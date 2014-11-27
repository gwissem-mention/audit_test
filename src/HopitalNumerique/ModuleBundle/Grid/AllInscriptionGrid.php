<?php

namespace HopitalNumerique\ModuleBundle\Grid;

use Nodevo\GridBundle\Grid\Column;
use HopitalNumerique\ModuleBundle\Grid\BaseInscriptionGrid;

/**
 * Configuration du grid Session de l'ensemble des modules.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class AllInscriptionGrid extends BaseInscriptionGrid
{
    /**
     * Définie la config spécifique au grid Session.
     */
    public function setConfig()
    {
        $this->setFunctionName('getAllDatasForGrid');
        $this->setSource( 'hopitalnumerique_module.manager.inscription' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('- Aucune inscription -');
        $this->setDefaultFilters( array('etatInscription' => 'En attente') );
    }

    /**
     * Ajoute les colonnes du grid Session.
     */
    public function setColumns()
    {
        $roles = $this->_container->get('nodevo_role.manager.role')->getRolesAsArray();


        $moduleColumn = new Column\TextColumn('moduleTitre', 'Module');
        $this->addColonne( $moduleColumn );
        
        $dateSessionColumn = new Column\DateColumn('dateSession', 'Date de début de la session');
        $this->addColonne( $dateSessionColumn );
        
        $prenomNomParticipantColumn = new Column\TextColumn('nomPrenom', 'Participant');
        $this->addColonne( $prenomNomParticipantColumn );
        
        $regionParticipantColumn = new Column\TextColumn('userRegion', 'Sa région');
        $this->addColonne( $regionParticipantColumn );
        
        $profilParticipantColumn = new Column\TextColumn('userProfil', 'Son profil');
        $this->addColonne( $profilParticipantColumn );
        
        $roleColumn = new Column\ArrayColumn('roles', 'Groupe associé');
        $roleColumn->manipulateRenderCell(
                function($value, $row, $router) use ($roles){
                    return array($roles[$value[0]]);
                }
        );
        $roleColumn->setFilterType('select');
        $roleColumn->setSelectFrom('values');
        $roleColumn->setOperatorsVisible( false );
        $roleColumn->setValues( $roles );
        $this->addColonne( $roleColumn );
        
        $this->addColonne( new Column\TextColumn('commentaire', 'Commentaire') );

        $etatInscriptionColumn = new Column\TextColumn('etatInscription', 'Etat de l\'inscription');
        $etatInscriptionColumn->setFilterType('select');
        $etatInscriptionColumn->setOperatorsVisible( false );
        $etatInscriptionColumn->setDefaultOperator( \APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ );
        $this->addColonne( $etatInscriptionColumn );

        $nbInscritsColumn = new Column\TextColumn('nbInscrits', 'Nombres d\'inscrits pour sa session');
        $this->addColonne( $nbInscritsColumn );
        
        $nbInscritsEnAttenteColumn = new Column\TextColumn('nbInscritsEnAttente', 'Nombres d\'inscrits en attente pour sa session');
        $this->addColonne( $nbInscritsEnAttenteColumn );
        
        $nbInscritsColumn = new Column\TextColumn('placeRestantes', 'Nombres de places restantes pour sa session');
        $this->addColonne( $nbInscritsColumn );
    }
}