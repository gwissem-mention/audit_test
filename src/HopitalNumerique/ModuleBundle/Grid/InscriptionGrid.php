<?php

namespace HopitalNumerique\ModuleBundle\Grid;

use Nodevo\GridBundle\Grid\Column;
use HopitalNumerique\ModuleBundle\Grid\BaseInscriptionGrid;

/**
 * Configuration du grid Inscription.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionGrid extends BaseInscriptionGrid
{
    /**
     * Définie la config spécifique au grid Inscription.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_module.manager.inscription' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        
        $this->setNoDataMessage('Aucune inscription à afficher.');
    }

    /**
     * Ajoute les colonnes du grid Inscription.
     */
    public function setColumns()
    {
        $roles = $this->_container->get('nodevo_role.manager.role')->getRolesAsArray();
        
        $prenomNomParticipantColumn = new Column\TextColumn('nomPrenom', 'Participant');
        $prenomNomParticipantColumn->setSize( 150 );
        $this->addColonne( $prenomNomParticipantColumn );
        
        $regionParticipantColumn = new Column\TextColumn('userRegion', 'Sa région');
        $regionParticipantColumn->setSize( 150 );
        $this->addColonne( $regionParticipantColumn );
        
        $profilParticipantColumn = new Column\TextColumn('userProfil', 'Son profil');
        $profilParticipantColumn->setSize( 150 );
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
        $etatInscriptionColumn->setSize( 170 );
        $etatInscriptionColumn->setFilterType('select');
        $etatInscriptionColumn->setOperatorsVisible( false );
        $this->addColonne( $etatInscriptionColumn );

        $etatParticipationColumn = new Column\TextColumn('etatParticipation', 'Etat de la participation');
        $etatParticipationColumn->setSize( 170 );
        $etatParticipationColumn->setFilterType('select');
        $etatParticipationColumn->setOperatorsVisible( false );
        $this->addColonne( $etatParticipationColumn );
        
        $etatEvaluationColumn = new Column\TextColumn('etatEvaluation', 'Etat de l\'évaluation');
        $etatEvaluationColumn->setSize( 170 );
        $etatEvaluationColumn->setFilterType('select');
        $etatEvaluationColumn->setOperatorsVisible( false );
        $this->addColonne( $etatEvaluationColumn );
    }
}