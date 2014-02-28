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
        $option = $this->get('hopitalnumerique_questionnaire.manager.reponse')->download($reponse);
        
        return $this->get('igorw_file_serve.response_factory')->create( $this->get('hopitalnumerique_questionnaire.manager.question')->getUploadRootDir($reponse->getQuestion()->getQuestionnaire()->getNomMinifie()) . '/'. $reponse->getReponse(), 'application/pdf', $option);
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
}
