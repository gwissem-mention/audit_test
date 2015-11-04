<?php
namespace HopitalNumerique\QuestionnaireBundle\Manager;

use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Manager de Occurrence.
 */
class OccurrenceManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $_class = 'HopitalNumerique\QuestionnaireBundle\Entity\Occurrence';
    
    /**
     * Retourne la dernière occurrence d'un questionnaire pour un utilisateur.
     * 
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire Questionnaire
     * @param \HopitalNumerique\UserBundle\Entity\User                   $user          Utilisateur
     * @return \HopitalNumerique\QuestionnaireBundle\Entity\Occurrence|NULL Dernière occurrence ou NULL si aucune occurrence
     */
    public function getDerniereOccurenceByQuestionnaireAndUser(Questionnaire $questionnaire, User $user)
    {
        if ($questionnaire->isOccurrenceMultiple())
        {
            $dernieresOccurences = $this->findBy(array('questionnaire' => $questionnaire, 'user' => $user), array('id' => 'DESC'), 1);

            return (count($dernieresOccurences) > 0 ? $dernieresOccurences[0] : null);
        }
        
        return null;
    }
}
