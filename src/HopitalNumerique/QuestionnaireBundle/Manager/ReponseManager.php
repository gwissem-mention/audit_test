<?php

namespace HopitalNumerique\QuestionnaireBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Contractualisation.
 */
class ReponseManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\QuestionnaireBundle\Entity\Reponse';
    
    /**
     * Récupère les réponses pour l'utilisateur en fonction du questionnaire passés en param
     *
     * @return array
     */
    public function reponsesByQuestionnaireByUser( $idQuestionnaire, $idUser, $orderByQuestion = false )
    {
        $reponses = $this->getRepository()->reponsesByQuestionnaireByUser( $idQuestionnaire , $idUser )->getResult();
        
        //Si on le spécifie, $reponses prendra en clé l'id de la question
        if($orderByQuestion)
        {
            $tempReponses = array();
            
            foreach ($reponses as $reponse)
                $tempReponses[$reponse->getQuestion()->getId()] = $reponse;
            
            $reponses = $tempReponses;
        }
        
        return $reponses;
    }
    
    /**
     * Récupère les réponses pour l'utilisateur en fonction du questionnaire passés en param pour les questions de type 'file'
     *
     * @return array
     */
    public function reponsesByQuestionnaireByUserByFileQuestion( $idQuestionnaire, $idUser )
    {    
        return $this->getRepository()->reponsesByQuestionnaireByUserByFileQuestion( $idQuestionnaire , $idUser )->getResult();
    }
    
    /**
     * Récupère les réponses pour l'utilisateur en fonction du questionnaire passés en param
     *
     * @return array Tableau sous la forme array(utilisateur => array(questionnaireId))
     */
    public function reponseExiste( $idExpert, $idAmbassadeur )
    {
        $result = array();
        $reponses = $this->getRepository()->reponseExiste( $idExpert , $idAmbassadeur )->getResult();
        
        foreach ($reponses as $key => $reponse)
        {
            if( key_exists($key, $reponses) )
            {
                $result[$reponse['userId']][] = $reponse['questId'];
            }
            else 
            {
                $result[$reponse['userId']] = array($reponse['questId']);
            }  
        }
        
        return $result;
    }
    
    /**
     * Supprime toutes les réponses pour un utilisateur correspondant au questionnaire passés en paramètre
     * 
     * @param int $idUser
     * @param int $idQuestionnaire
     * 
     * @return empty
     */
    public function deleteAll( $idUser, $idQuestionnaire)
    {
        $reponses = $this->getRepository()->reponsesByQuestionnaireByUser( $idQuestionnaire , $idUser )->getResult();
        
        $this->delete($reponses);
    }
    
    /**
     * Téléchargement des fichiers attaché au questionnaire expert.
     *
     * @param int $id Id de la réponse du fichier à télécharger
     */
    public function download( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $reponse = $this->findOneBy( array( 'id' => $id) );
        
        $options = array(
                'serve_filename' => $reponse->getReponse(),
                'absolute_path' => false,
                'inline' => false,
        );
        
        return $options;
    }
}