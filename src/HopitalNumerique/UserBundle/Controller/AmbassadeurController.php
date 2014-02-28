<?php
namespace HopitalNumerique\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nodevo\ToolsBundle\Tools\Chaine;
use Nodevo\RoleBundle\Entity\Role;
use HopitalNumerique\QuestionnaireBundle\Manager;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;

/**
 * Controller des abassadeurs
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class AmbassadeurController extends Controller
{
    /**
     * Affichage du formulaire d'utilisateur
     * 
     * @param integer $id Identifiant de l'utilisateur
     */
    public function editAction( HopiUser $user )
    {        
        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = Manager\QuestionnaireManager::_getQuestionnaireId('ambassadeur');
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireExpert) );        

        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:edit.html.twig',array(
                'questionnaire' => $questionnaire,
                'user'          => $user,
                'options'       => $this->_gestionAffichageOnglet($user),
                'routeRedirect' => json_encode(array(
                    'quit' => array(
                        'route'     => 'hopital_numerique_user_homepage',
                        'arguments' => array()
                    ),
                    'sauvegarde' => array(
                        'route'     => 'hopitalnumerique_user_ambassadeur_edit',
                        'arguments' => array(
                                'id' => $user->getId()
                        )
                    )
                ))
        ));
    }


    /**
     * Affichage de la fiche des réponses au questionnaire ambassadeur d'un utilisateur
     *
     * @param integer $idUser          ID de l'utilisateur
     */
    public function showAction( $idUser )
    {
        //Récupération du questionnaire de l'expert
        $idQuestionnaireAmbassadeur = Manager\QuestionnaireManager::_getQuestionnaireId('ambassadeur');
    
        //Récupération de l'utilisateur passé en param
        $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( $idQuestionnaireAmbassadeur , $idUser );
    
        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:show.html.twig', array(
                'reponses'       => $reponses,
                'nombreReponses' => count($reponses)
        ));
    }
    
    /**
     * Affichage de la liste des objets d'un utilisateur
     *
     * @param integer $idUser          ID de l'utilisateur
     */
    public function listeObjetsAction( $idUser )
    {    
        //Récupération de l'utilisateur passé en param
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByAmbassadeur($idUser);
    
        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:liste_objets.html.twig', array(
                'objets'       => $objets,
                'nombreObjets' => count($objets)
        ));
    }
    
    /**
     * Affiche la liste des objets maitrisés par l'ambassadeur
     *
     * @param integer $id ID de l'ambassadeur
     *
     * @return Response
     */
    public function objetsAction( $id )
    {
        //Récupération de l'utilisateur passé en param
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );
        
        $grid = $this->get('hopitalnumerique_user.grid.objet');
        $grid->setSourceCondition('ambassadeur', $id);

        return $grid->render('HopitalNumeriqueUserBundle:Ambassadeur:objets.html.twig',array(
            'user'    => $user,
            'options' => $this->_gestionAffichageOnglet($user)
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
        
        $this->get('session')->getFlashBag()->add( 'success' ,  'Le questionnaire d\'ambassadeur a été vidé.' );

        return new Response('{"success":true, "url" : "'. $this->generateUrl('hopitalnumerique_user_ambassadeur_edit', array('id' => $idUser)).'"}', 200);
    }

    /**
     * Supprime le lien objet => ambassadeur
     *
     * @param int $id   ID de l'objet
     * @param int $user ID de l'user
     */
    public function deleteObjetAction( $id, $user )
    {
        $ambassadeur = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $user) );
        $objet       = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        $objet->removeAmbassadeur( $ambassadeur );
        $this->get('hopitalnumerique_objet.manager.objet')->save( $objet );

        $this->get('session')->getFlashBag()->add( 'success' ,  'La production n\'est plus maitrisée par l\'ambassadeur.' );

        return new Response('{"success":true, "url" : "'. $this->generateUrl('hopitalnumerique_user_ambassadeur_objets', array('id' => $user)).'"}', 200);
    }

    /**
     * Fancybox d'ajout d'objet à l'utilisateur
     *
     * @param integer $id ID de l'ambassadeur
     */
    public function addObjetAction( $id )
    {
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsNonMaitrises( $id );
        
        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:add_objet.html.twig', array(
            'objets'      => $objets,
            'ambassadeur' => $id
        ));
    }

    /**
     * Sauvegarde AJAX de la liaison objet + ambassadeur
     */
    public function saveObjetAction()
    {
        //get posted vars
        $id     = $this->get('request')->request->get('ambassadeur');
        $objets = $this->get('request')->request->get('objets');

        //bind ambassadeur
        $ambassadeur = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );

        //bind objects
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->findBy( array( 'id' => $objets ) );
        foreach($objets as &$objet)
            $objet->addAmbassadeur( $ambassadeur );
        
        $this->get('hopitalnumerique_objet.manager.objet')->save( $objets );
        
        $this->get('session')->getFlashBag()->add( 'success' ,  'Les productions ont été liées à l\'ambassadeur.' );

        return new Response('{"success":true, "url" : "'. $this->generateUrl('hopitalnumerique_user_ambassadeur_objets', array('id' => $id)).'"}', 200);
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
