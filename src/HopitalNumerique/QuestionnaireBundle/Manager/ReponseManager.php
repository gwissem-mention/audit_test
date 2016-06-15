<?php

namespace HopitalNumerique\QuestionnaireBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use HopitalNumerique\QuestionnaireBundle\Entity\Occurrence;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Manager de l'entité Contractualisation.
 */
class ReponseManager extends BaseManager
{
    protected $class = 'HopitalNumerique\QuestionnaireBundle\Entity\Reponse';
    
    /**
     * Récupère les réponses pour l'utilisateur en fonction du questionnaire passés en param
     *
     * @return array
     */
    public function reponsesByQuestionnaireByUser( $idQuestionnaire, $idUser, $orderByQuestion = false, Occurrence $occurrence = null, $paramId = null )
    {
        $reponses = $this->getRepository()->reponsesByQuestionnaireByUser( $idQuestionnaire , $idUser, $occurrence, $paramId )->getResult();
        
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
    public function reponsesByQuestionnaireByUserByFileQuestion( $idQuestionnaire, $idUser, Occurrence $occurrence = null )
    {    
        return $this->getRepository()->reponsesByQuestionnaireByUserByFileQuestion( $idQuestionnaire , $idUser, $occurrence )->getResult();
    }

    public function getReponsesForQuestionnaireOrderByUser($idQuestionnaire)
    {
        $reponses              = $this->getRepository()->getReponsesForQuestionnaireOrderByUser( $idQuestionnaire )->getResult();
        $reponsesOrderedByUser = array();

        foreach ($reponses as $reponse) 
        {
            if(!array_key_exists($reponse->getUser()->getId(), $reponsesOrderedByUser))
            {
                $reponsesOrderedByUser[$reponse->getUser()->getId()] = array();
            } 

            $valueReponse = "";

            switch($reponse->getQuestion()->getTypeQuestion()->getLibelle())
            {
                case 'entityradio':
                case 'entity':
                    $valueReponse = (!is_null($reponse->getReference())) ? $reponse->getReference()->getLibelle() : '';
                    break;
                case 'checkbox':
                    $valueReponse = ('1' == $reponse->getReponse()) ? 'Oui' : 'Non' ;
                    break;
                //Gestion très sale, à revoir au moment de la construction du tableau de réponses avec des niveaux d'enfants/parents etc.
                case 'entitymultiple':
                case 'entitycheckbox':
                    //Affichage pour une possibilité de plusieurs réponses à cette question
                    foreach ($reponse->getReferenceMulitple() as $key => $referenceMultiple) 
                    {
                        $valueReponse .= $referenceMultiple->getLibelle() . ' ';
                    }
                    break;
                default:
                    $valueReponse = $reponse->getReponse();
                    break;
            }
            $reponsesOrderedByUser[$reponse->getUser()->getId()][$reponse->getQuestion()->getId()] = $valueReponse;
        }

        return $reponsesOrderedByUser;
    }
    
    /**
     * Récupère les réponses pour l'utilisateur en fonction du questionnaire passés en param
     * 
     * @param int $idExpert      Identifiant du questionnaire expert
     * @param int $idAmbassadeur Identifiant du questionnaire ambassadeur
     *
     * @return array Tableau sous la forme array(utilisateur => array(questionnaireId))
     */
    public function reponseExiste( $idExpert, $idAmbassadeur )
    {
        $result = array();
        $reponses = $this->getRepository()->reponseExiste( $idExpert , $idAmbassadeur )->getResult();
        
        foreach ($reponses as $key => $reponse)
        {
            if( array_key_exists($key, $reponses) )
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
        
        foreach($reponses as $key => $reponse)
        {
            if('file' === $reponse->getQuestion()->getTypeQuestion()->getLibelle())
            {
                $file = $this->getUploadRootDir($reponse->getQuestion()->getQuestionnaire()->getNomMinifie()) . '/' . $reponse->getReponse();
                
                if (file_exists($file) )
                    unlink($file);
            }
        }
        
        $this->delete($reponses);
    }


    
    /**
     * Supprime toutes les réponses pour tout les utilisateurs correspondant au questionnaire passé en paramètre
     * 
     * @param int $idUser
     * @param int $idQuestionnaire
     * 
     * @return empty
     */
    public function deleteAllByQuestionnaire( $idQuestionnaire)
    {
        $reponses = $this->getRepository()->reponsesByQuestionnaire( $idQuestionnaire )->getResult();
        
        foreach($reponses as $key => $reponse)
        {
            if('file' === $reponse->getQuestion()->getTypeQuestion()->getLibelle())
            {
                $file = $this->getUploadRootDir($reponse->getQuestion()->getQuestionnaire()->getNomMinifie()) . '/' . $reponse->getReponse();
                
                if (file_exists($file) )
                    unlink($file);
            }
        }
        
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
    
    /**
     * Retourne la path de l'endroit où on doit upload un fichier
     *
     * @param string $questionnaire
     * @return string Chemin root du fichier à uploader
     */
    public function getUploadRootDir( $labelQuestionnaire )
    {
        if(!file_exists(__ROOT_DIRECTORY__.'/files/'.$labelQuestionnaire))
            return null;
    
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __ROOT_DIRECTORY__.'/files/'.$labelQuestionnaire;
    }
    
    
    /**
     * Affecte une occurrence à toutes les réponses d'un questionnaire répondu par un utilisateur.
     * 
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Occurrence    $occurrence    Occurrence
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire Questionnaire
     * @param \HopitalNumerique\UserBundle\Entity\User                   $user          User
     * @return void
     */
    public function setOccurrenceByQuestionnaireAndUser(Occurrence $occurrence, Questionnaire $questionnaire, User $user)
    {
        $this->getRepository()->setOccurrenceByQuestionnaireAndUser($occurrence, $questionnaire, $user);
    }
}
