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
        $idQuestionnaireExpert = Manager\QuestionnaireManager::_getQuestionnaireId('expert');
        
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
        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = Manager\QuestionnaireManager::_getQuestionnaireId('expert');
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireExpert) );

        return $this->render('HopitalNumeriqueUserBundle:Expert:edit.html.twig',array(
                'questionnaire' => $questionnaire,
                'user'          => $user,
                'options'       => $this->_gestionAffichageOnglet($user),
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

    /**
     * Suppression de toutes les réponses de l'utilisateur pour le questionnaire passé en param
     *
     * @param int $idUser
     * @param int $idQuestionnaire
     */
    public function deleteAllAction( $idUser, $idQuestionnaire )
    {
        $this->get('hopitalnumerique_questionnaire.manager.reponse')->deleteAll( $idUser, $idQuestionnaire);
    
        $this->get('session')->getFlashBag()->add( 'success' ,  'Le questionnaire d\'expert a été vidé.' );
    
        return new Response('{"success":true, "url" : "'. $this->generateUrl('hopitalnumerique_user_expert_edit', array('id' => $idUser)).'"}', 200);
    }

    /**
     * Téléchargement des fichiers attaché au questionnaire expert.
     * 
     * @param int $id Id de la réponse du fichier à télécharger
     */
    public function dowloadAction( $id )
    {        
        //Récupération de l'entité en fonction du paramètre
        $reponse = $this->get('hopitalnumerique_questionnaire.manager.reponse')->findOneBy( array( 'id' => $id) );
    
        $options = array(
                'serve_filename' => $reponse->getReponse(),
                'absolute_path' => false,
                'inline' => false,
        );
    
        return $this->get('igorw_file_serve.response_factory')->create( $this->get('hopitalnumerique_questionnaire.manager.question')->getUploadRootDir('expert') . '/'. $reponse->getReponse(), 'application/pdf', $options);
    }
    
    /**
     * Fonction permettant d'envoyer un tableau d'option à la vue pour vérifier le role de l'utilisateur
     *
     * @param User $user
     * @return array
     */
    private function _gestionAffichageOnglet( $user )
    {
        $roles = $user->getRoles();
        $options = array(
                'ambassadeur' => false,
                'expert'      => false
        );

        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = Manager\QuestionnaireManager::_getQuestionnaireId('expert');
        //Récupération du questionnaire de l'ambassadeur
        $idQuestionnaireAmbassadeur = Manager\QuestionnaireManager::_getQuestionnaireId('ambassadeur');
        
        //Récupération des réponses du questionnaire expert de l'utilisateur courant
        $reponsesExpert      = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser($idQuestionnaireExpert, $user->getId());
        //Récupération des réponses du questionnaire ambassadeur de l'utilisateur courant
        $reponsesAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser($idQuestionnaireAmbassadeur, $user->getId());

        //Si il y a des réponses correspondant au questionnaire du groupe alors on donne l'accès
        $options['expert_form']      = !empty($reponsesExpert);
        $options['ambassadeur_form'] = !empty($reponsesAmbassadeur);
        
        //Dans tout les cas si l'utilisateur a le bon groupe on lui donne l'accès
        foreach ($roles as $key => $role)
        {
            switch ($role->getRole())
            {
                case 'ROLE_EXPERT_6':
                    $options['expert'] = true;
                    break;
                case 'ROLE_AMBASSADEUR_7':
                    $options['ambassadeur'] = true;
                    break;
                default:
                    break;
            }
        }
    
        return $options;
    }
}