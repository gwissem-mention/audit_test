<?php

namespace HopitalNumerique\ModuleBundle\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;
use HopitalNumerique\ModuleBundle\Entity\Session;

class EvaluationController extends Controller
{
    /**
      * Affichage du formulaire d'utilisateur.
      *
      * @param int $id Identifiant de l'utilisateur
      */
     public function editAction(HopiUser $user, Session $session)
     {
         //Récupération du questionnaire de l'expert
        $idQuestionnaireEvaluationModule = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('module-evaluation');
         $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy(['id' => $idQuestionnaireEvaluationModule]);

         return $this->render('HopitalNumeriqueModuleBundle:Back/Evaluation:edit.html.twig', [
            'questionnaire' => $questionnaire,
            'user' => $user,
            'session' => $session,
            'optionRenderForm' => [
                'themeQuestionnaire' => 'vertical',
                'session' => $session->getId(),
                'envoieDeMail' => false,
                'readOnly' => false,
                'routeRedirect' => json_encode([
                    'quit' => [
                        'route' => 'hopitalnumerique_module_module',
                        'arguments' => [],
                    ],
                    'sauvegarde' => [
                        'route' => 'hopitalnumerique_module_module_session_evaluation_editer',
                        'arguments' => [
                            'user' => $user->getId(),
                            'session' => $session->getId(),
                        ],
                    ],
                ]),
            ],
        ]);
     }
}
