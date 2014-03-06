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

    //---- Front Office ------
    /**
     * Affichage du formulaire d'utilisateur
     * 
     * @param integer $id Identifiant de l'utilisateur
     */
    public function editAction( HopiUser $user )
    {        
        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireExpert) );

        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:edit.html.twig',array(
                'questionnaire' => $questionnaire,
                'user'          => $user,
                'options'       => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
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
    
    //---- Back Office ------
    /**
     * Affichage du formulaire d'utilisateur
     *
     * @param integer $id Identifiant de l'utilisateur
     */
     public function editAction( HopiUser $user )
     {
     //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireExpert) );
    
        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:edit.html.twig',array(
                'questionnaire' => $questionnaire,
                    'user'          => $user,
                    'options'       => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
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
        $idQuestionnaireAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');
    
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
            'options' => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user)
        ));
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
}
