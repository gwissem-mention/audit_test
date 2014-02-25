<?php

namespace HopitalNumerique\QuestionnaireBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Contractualisation.
 */
class QuestionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\QuestionnaireBundle\Entity\Question';
    
    /**
     * Recupère toutes les questions du type passé en paramètre pour le questionnaire $idQuestionnaire
     *
     * @param int    $idQuestionnaire
     * @param string $typeQuestion
     *
     * @return array(\HopitalNumerique\QuestionnaireBundle\Entity\Questions) Tableau des questions de type $typeQuestion
     */
    public function getQuestionsByType( $idQuestionnaire, $typeQuestion )
    {
        return $this->getRepository()->getQuestionsByType( $idQuestionnaire, $typeQuestion );
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
            //throw new \Exception('Le dossier ' . __ROOT_DIRECTORY__.'/files/'.$labelQuestionnaire . ' n\'existe pas.');
            return null;
            
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __ROOT_DIRECTORY__.'/files/'.$labelQuestionnaire;
    }
}