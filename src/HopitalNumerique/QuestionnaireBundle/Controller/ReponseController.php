<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use Symfony\Component\HttpFoundation\Response;

class ReponseController extends Controller
{
    /**
     * Téléchargement des fichiers attaché au questionnaire expert.
     *
     * @param int $id Id de la réponse du fichier à télécharger
     */
    public function dowloadReponseAction( Reponse $reponse )
    {                
        if(file_exists($this->get('hopitalnumerique_questionnaire.manager.question')->getUploadRootDir($reponse->getQuestion()->getQuestionnaire()->getNomMinifie()) . '/'. $reponse->getReponse()))
        {        
            $option = $this->get('hopitalnumerique_questionnaire.manager.reponse')->download($reponse);
            
            return $this->get('igorw_file_serve.response_factory')->create( $this->get('hopitalnumerique_questionnaire.manager.question')->getUploadRootDir($reponse->getQuestion()->getQuestionnaire()->getNomMinifie()) . '/'. $reponse->getReponse(), 'application/pdf', $option);
        }
        else
        {     
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('danger') , 'Le document n\'existe plus sur le serveur.' );
        
            //Si l'url courante contient le mot clé "admin"
            if (strpos($this->get('request')->getPathInfo(),'admin') !== false) 
                //BackOffice
                return $this->redirect( $this->generateUrl('hopital_numerique_user_homepage') );
            else
                //FrontOffice
                return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
        }
    }
    
    /**
     * Suppression de toutes les réponses de l'utilisateur pour le questionnaire passé en param
     *
     * @param int $idUser
     * @param int $idQuestionnaire
     */
    public function deleteAllAction( HopiUser $user, Questionnaire $questionnaire )
    {
        $routeRedirection = $this->get('request')->request->get('routeRedirection');
        $routeRedirection = json_decode($routeRedirection, true);
        
        $this->get('hopitalnumerique_questionnaire.manager.reponse')->deleteAll( $user->getId(), $questionnaire->getId());
    
        $this->get('session')->getFlashBag()->add( 'success' ,  'Le questionnaire '. $questionnaire->getNomMinifie() .' a été vidé.' );
        
        return new Response('{"success":true, "url" : "'.$this->generateUrl($routeRedirection['sauvegarde']['route'], $routeRedirection['sauvegarde']['arguments']).'"}', 200);
    }
    
    /**
     * Suppression de toutes les réponses de tout les utilisateurs pour le questionnaire passé en param
     *
     * @param int $idQuestionnaire
     */
    public function deleteAllByQuestionnaireAction( Questionnaire $questionnaire )
    {
        //Suppression des entitées
        $this->get('hopitalnumerique_questionnaire.manager.reponse')->deleteAllByQuestionnaire( $questionnaire->getId() );

        $this->get('session')->getFlashBag()->add( 'success' ,  'Le questionnaire '. $questionnaire->getNomMinifie() .' a été vidé de ses réponses.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_questionnaire_index').'"}', 200);
    }
}
