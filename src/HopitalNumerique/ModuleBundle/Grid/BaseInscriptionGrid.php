<?php

namespace HopitalNumerique\ModuleBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Session de l'ensemble des modules.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class BaseInscriptionGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Session.
     */
    public function setConfig()
    {
    }

    /**
     * Ajoute les colonnes du grid Session.
     */
    public function setColumns()
    {
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