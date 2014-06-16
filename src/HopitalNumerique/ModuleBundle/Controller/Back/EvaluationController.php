<?php

namespace HopitalNumerique\ModuleBundle\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;
use HopitalNumerique\ModuleBundle\Entity\Session;

class EvaluationController extends Controller
{
    /**
     * Affichage du formulaire d'utilisateur
     *
     * @param integer $id Identifiant de l'utilisateur
     */
     public function editAction( HopiUser $user, Session $session )
     {
        //Récupération du questionnaire de l'expert
        $idQuestionnaireEvaluationModule = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('module-evaluation');
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireEvaluationModule) );
            
        return $this->render('HopitalNumeriqueModuleBundle:Back/Evaluation:edit.html.twig',array(
            'questionnaire'    => $questionnaire,
            'user'             => $user,
            'session'          => $session,
            'optionRenderForm' => array(
                'themeQuestionnaire' => 'vertical',
                'session'            => $session->getId(),
                'envoieDeMail'       => false,
                'readOnly'           => false,
                'routeRedirect'      => json_encode(array(
                    'quit'              => array(
                        'route'             => 'hopitalnumerique_module_module',
                        'arguments'         => array()
                    ),
                    'sauvegarde'        => array(
                        'route'             => 'hopitalnumerique_module_module_session_evaluation_editer',
                        'arguments'         => array(
                            'user'                => $user->getId(),
                            'session'             => $session->getId()
                        )
                    )
                ))
            )
        ));
    }
}
