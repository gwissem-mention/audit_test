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
    // ----- Front office -----

    /**
     * Met en place une vue pour accueillir la vue du formulaire QuestionnaireBundle
     *
     * @param integer $id Identifiant de l'utilisateur
     */
    public function editFrontAction( HopiUser $user )
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();
        
        $manager = $this->get('hopitalnumerique_questionnaire.manager.questionnaire');
    
        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = $manager->getQuestionnaireId('expert');
        $questionnaire = $manager->findOneBy( array('id' => $idQuestionnaireExpert) );
        
        //Récupération des réponses pour le questionnaire et utilisateur courant, triées par idQuestion en clé
        $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( $questionnaire->getId(), $user->getId(), true );
        
        $themeQuestionnaire = empty($reponses) ? 'vertical' : 'vertical_readonly';
        //readonly si il y a des réponses dans le questionnaire ou que le role courant de l'utilisateur est expert
        $readOnly = (in_array('ROLE_EXPERT_6', $user->getRoles()) || !empty($reponses));
    
        return $this->render('HopitalNumeriqueUserBundle:Expert/Front:edit.html.twig',array(
                'questionnaire'      => $questionnaire,
                'user'               => $user,
                'optionRenderForm'   => array(
                        'readOnly'           => $readOnly,
                        'themeQuestionnaire' => $themeQuestionnaire,
                        'routeRedirect'      => json_encode(array(
                                'quit' => array(
                                        'route'     => 'hopitalnumerique_user_expert_front_edit',
                                        'arguments' => array()
                                )
                        ))
                )
        ));
    }
    
    // ----- Back office ----
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
                'optionRenderForm'   => array(
                        'routeRedirect'      => json_encode(array(
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
                )
        ));
    }
    
    /**
     * Validation de la candidature de l'utilisateur pour le questionnaire
     *
     * @param int $user
     */
    public function validationCandidatureAction( HopiUser $user )
    {
        $routeRedirection = $this->get('request')->request->get('routeRedirection');
        $routeRedirection = json_decode($routeRedirection, true);
    
        //Récupération du questionnaire de l'ambassadeur
        $idQuestionnaireAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireAmbassadeur) );
    
        //Changement du rôle à ambassadeur de l'utilisateur
        $role = $this->get('nodevo_role.manager.role')->findOneBy(array('role' => 'ROLE_EXPERT_6'));
        $user->setRoles( array( $role ) );
    
        //Envoie du mail de validation de la candidature
        $mail = $this->get('nodevo_mail.manager.mail')->sendValidationCandidatureExpertMail($user);
        $this->get('mailer')->send($mail);
    
        //Mise à jour / création de l'utilisateur
        $this->get('fos_user.user_manager')->updateUser( $user );
    
        $this->get('session')->getFlashBag()->add( 'success' ,  'La candidature au poste '. $questionnaire->getNomMinifie() .' a été validé.' );
    
        return new Response('{"success":true, "url" : "'.$this->generateUrl($routeRedirection['sauvegarde']['route'], $routeRedirection['sauvegarde']['arguments']).'"}', 200);
    }
    
    /**
     * Refus de la candidature de l'utilisateur pour le questionnaire
     *
     * @param int $idUser
     * @param int $idQuestionnaire
     */
    public function refusCandidatureAction( HopiUser $user )
    {
        $routeRedirection = $this->get('request')->request->get('routeRedirection');
        $routeRedirection = json_decode($routeRedirection, true);
        
        //Texte du refus entré dans la fancybox
        $texteRefus = $this->get('request')->request->get('texteRefus');
        
        //Récupération du questionnaire de l'ambassadeur
        $idQuestionnaireExpert = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('expert');
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireExpert) );
        
        //Ajout en base du message de refus
        $refusCandidature = $this->get('hopitalnumerique_user.manager.refus_candidature')->createEmpty();
        $refusCandidature->setQuestionnaire($questionnaire);
        $refusCandidature->setUser($user);
        //Récupère l'utilsateur connecté
        $refusCandidature->setUserOrigineRefus($this->get('security.context')->getToken()->getUser());
        $refusCandidature->setMotifRefus($texteRefus);
        $refusCandidature->setDateRefus(new \DateTime());
        
        $this->get('hopitalnumerique_user.manager.refus_candidature')->save($refusCandidature);
        
        //Envoie du mail de validation de la candidature
        $mail = $this->get('nodevo_mail.manager.mail')->sendRefusCandidatureExpertMail($user, $texteRefus);
        $this->get('mailer')->send($mail);
        
        $this->get('session')->getFlashBag()->add( 'success' ,  'La candidature au poste '. $questionnaire->getNomMinifie() .' a été refusé.' );
        
        return new Response('{"success":true, "url" : "'.$this->generateUrl($routeRedirection['sauvegarde']['route'], $routeRedirection['sauvegarde']['arguments']).'"}', 200);
    }

    /**
     * POP-IN de message de refus
     */
    public function messageRefusCandidatureAction()
    {
        return $this->render('HopitalNumeriqueUserBundle:Expert:popin_refus_candidature.html.twig');
    }
}