<?php
namespace HopitalNumerique\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nodevo\ToolsBundle\Tools\Chaine;
use Nodevo\RoleBundle\Entity\Role;
use HopitalNumerique\QuestionnaireBundle\Manager;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;

/**
 * Controller des experts
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ExpertController extends Controller
{
    /**
     * Affichage de la fiche des réponses au questionnaire expert d'un utilisateur
     *
     * @param integer $idUser          ID de l'utilisateur
     */
    public function showAction( $idUser )
    {
        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('expert');
        
        //Récupération de l'utilisateur passé en param
        $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( $idQuestionnaireExpert , $idUser );
        
        return $this->render('HopitalNumeriqueUserBundle:Expert:show.html.twig', array(
                'reponses'  => $reponses,
                'nombreReponses' => count($reponses)
        ));
    }
    
    /**
     * Met en place une vue pour accueillir la vue du formulaire QuestionnaireBundle
     * 
     * @param integer $id Identifiant de l'utilisateur
     */
    public function editAction( HopiUser $user )
    {                
        $manager = $this->get('hopitalnumerique_questionnaire.manager.questionnaire');

        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = $manager->getQuestionnaireId('expert');
        $questionnaire = $manager->findOneBy( array('id' => $idQuestionnaireExpert) );

        return $this->render('HopitalNumeriqueUserBundle:Expert:edit.html.twig',array(
                'questionnaire' => $questionnaire,
                'user'          => $user,
                'options'       => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
                'routeRedirect' => json_encode(array(
                    'quit' => array(
                        'route'     => 'hopital_numerique_user_homepage',
                        'arguments' => array()
                    ),
                    'sauvegarde' => array(
                        'route'     => 'hopitalnumerique_user_expert_edit',
                        'arguments' => array(
                                'id' => $user->getId()
                        )
                    )
                ))
        ));
    }
}