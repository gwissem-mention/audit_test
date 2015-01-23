<?php

namespace HopitalNumerique\QuestionnaireBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid Facture.
 */
class QuestionnaireGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid Questionnaire.
     */
    public function setConfig()
    {   
        //Manager
        $this->setSource( 'hopitalnumerique_questionnaire.manager.questionnaire' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setButtonSize( 43 );
        
        $this->setNoDataMessage('Aucun questionnaire à afficher.');
        $this->showIDColumn( true );
        $this->setFilterIdColumn( true );
    }

    /**
     * Ajoute les colonnes du grid Questionnaire.
     */
    public function setColumns()
    {
        $this->addColonne( new Column\TextColumn('nom', 'Nom du questionnaire') );

        $nbRepondantColumn = new Column\TextColumn('nbUser', 'Nombre de répondant');
        $nbRepondantColumn->setSize( 150 );
        $this->addColonne( $nbRepondantColumn );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {        
        $this->addActionButton( new Action\EditButton( 'hopitalnumerique_questionnaire_edit_questionnaire' ) );

        $listQuestionsButton = new \APY\DataGridBundle\Grid\Action\RowAction('', 'hopitalnumerique_questionnaire_question_index');
        $listQuestionsButton->setRouteParameters( array('id') );
        $listQuestionsButton->setAttributes( array('class'=>'btn btn-success fa fa-list','title' => 'Lister les questions') );
        $this->addActionButton( $listQuestionsButton );

        $viderReponsesButton = new Action\ShowButton( 'hopitalnumerique_reponse_deleteAllByQuestionnaire' );
        $viderReponsesButton->setRouteParameters( array('id') );
        $viderReponsesButton->setAttributes( array(
                'class'=>'btn btn-primary fa fa-trash-o',
                'title' => 'Vider l\'ensemble des réponses du questionnaire.'
        ));
        $this->addActionButton( $viderReponsesButton );
        
        // $this->addActionButton( new Action\DeleteButton( 'hopitalnumerique_questionnaire_delete_questionnaire' ) );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Supprimer', 'HopitalNumeriqueQuestionnaireBundle:Questionnaire:deleteMass') );
    }
}