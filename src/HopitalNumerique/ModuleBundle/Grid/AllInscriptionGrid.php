<?php

namespace HopitalNumerique\ModuleBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Session de l'ensemble des modules.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class AllInscriptionGrid extends Grid implements IGrid
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
        // $dateSessionColumn->setSize( 150 );
        $this->addColonne( $dateSessionColumn );
        
        $prenomNomParticipantColumn = new Column\TextColumn('nomPrenom', 'Participant');
        // $prenomNomParticipantColumn->setSize( 150 );
        $this->addColonne( $prenomNomParticipantColumn );
        
        $regionParticipantColumn = new Column\TextColumn('userRegion', 'Sa région');
        // $regionParticipantColumn->setSize( 150 );
        $this->addColonne( $regionParticipantColumn );
        
        $profilParticipantColumn = new Column\TextColumn('userProfil', 'Son profil');
        // $profilParticipantColumn->setSize( 150 );
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
        // $etatInscriptionColumn->setSize( 170 );
        $etatInscriptionColumn->setFilterType('select');
        $etatInscriptionColumn->setOperatorsVisible( false );
        $this->addColonne( $etatInscriptionColumn );

        $nbInscritsColumn = new Column\TextColumn('nbInscrits', 'Nombres d\'inscrits pour sa session');
        $this->addColonne( $nbInscritsColumn );
        
        $nbInscritsEnAttenteColumn = new Column\TextColumn('nbInscritsEnAttente', 'Nombres d\'inscrits en attente pour sa session');
        $this->addColonne( $nbInscritsEnAttenteColumn );
        
        $nbInscritsColumn = new Column\TextColumn('placeRestantes', 'Nombres de places restantes pour sa session');
        $this->addColonne( $nbInscritsColumn );

        // $etatParticipationColumn = new Column\TextColumn('etatParticipation', 'Etat de la participation');
        // $etatParticipationColumn->setSize( 170 );
        // $etatParticipationColumn->setFilterType('select');
        // $etatParticipationColumn->setOperatorsVisible( false );
        // $this->addColonne( $etatParticipationColumn );
        
        // $etatEvaluationColumn = new Column\TextColumn('etatEvaluation', 'Etat de l\'évaluation');
        // $etatEvaluationColumn->setSize( 170 );
        // $etatEvaluationColumn->setFilterType('select');
        // $etatEvaluationColumn->setOperatorsVisible( false );
        // $this->addColonne( $etatEvaluationColumn );
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
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Accepter inscription'  ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:accepterInscriptionMass') );

        $actionMassRefusInscription = new Action\ActionMass('Refuser inscription'   ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:refuserInscriptionMass');
        $this->addMassAction( $actionMassRefusInscription );
        
        $this->addMassAction( new Action\ActionMass('Annuler inscription'   ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:annulerInscriptionMass') );
        $this->addMassAction( new Action\ActionMass('A participé'           ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:aParticiperParticipationMass') );
        $this->addMassAction( new Action\ActionMass('N\'a pas participé'    ,'HopitalNumeriqueModuleBundle:Back/InscriptionMass:aPasParticiperParticipationMass') );
        /* Exports */
        $this->addMassAction( new Action\ActionMass('Export CSV - Inscriptions', 'HopitalNumeriqueModuleBundle:Back/InscriptionMass:exportCsv') );
        $this->addMassAction( new Action\ActionMass('Envoyer un mail'          , 'HopitalNumeriqueModuleBundle:Back/InscriptionMass:envoyerMailMass') );
    }
}