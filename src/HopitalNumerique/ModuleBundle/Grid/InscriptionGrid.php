<?php

namespace HopitalNumerique\ModuleBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Inscription.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionGrid extends Grid implements IGrid
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

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $actionFicheParticipant = new Action\ShowButton('hopital_numerique_user_show');
        $actionFicheParticipant->setAttributes( array(
                'class'=>'btn btn-primary fa fa-user-md',
                'title' => 'Fiche de l\'utilisateur',
                'target' => '_blank'
        ));
        $actionFicheParticipant->setRouteParametersMapping(array('userId' => 'id'));
        $actionFicheParticipant->setRouteParameters(array('userId'));
        $this->addActionButton( $actionFicheParticipant );

        $actionFicheEvaluation = new Action\ShowButton('hopitalnumerique_module_module_session_evaluation_editer');
        $actionFicheEvaluation->setAttributes( array(
                'class'=>'btn btn-primary fa fa-list',
                'title' => 'Afficher le formulaire d\'évaluation',
                'target' => '_blank'
        ));
        $actionFicheEvaluation->setRouteParametersMapping(array('userId' => 'user', 'sessionId' => 'session'));
        $actionFicheEvaluation->setRouteParameters(array('userId', 'sessionId'));
        $this->addActionButton( $actionFicheEvaluation );
        $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_module_module_session_inscription_delete' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Inscription - Accepter inscription'  ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:accepterInscriptionMass') );

        $actionMassRefusInscription = new Action\ActionMass('Inscription - Refuser inscription'   ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:refuserInscriptionMass');
        $this->addMassAction( $actionMassRefusInscription );
        $this->addMassAction( new Action\ActionMass('Inscription   - Annuler inscription'   ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:annulerInscriptionMass') );

        $this->addMassAction( new Action\ActionMass('Participation - A participé'           ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:aParticiperParticipationMass') );
        $this->addMassAction( new Action\ActionMass('Participation - N\'a pas participé'    ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:aPasParticiperParticipationMass') );
        
        $this->addMassAction( new Action\ActionMass('Evaluation - A évaluer'           ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:aEvaluaerEvaluationMass') );
        $this->addMassAction( new Action\ActionMass('Evaluation - Évaluée'    ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:evalueeEvaluationMass') );
        $this->addMassAction( new Action\ActionMass('Evaluation - NA'    ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:naEvaluationMass') );
        
        /* Exports */
        $this->addMassAction( new Action\ActionMass('Export CSV - Inscriptions', 'HopitalNumeriqueModuleBundle:Back/InscriptionMass:exportCsv') );
        $this->addMassAction( new Action\ActionMass('Envoyer un mail'          , 'HopitalNumeriqueModuleBundle:Back/InscriptionMass:envoyerMailMass') );
    }
}