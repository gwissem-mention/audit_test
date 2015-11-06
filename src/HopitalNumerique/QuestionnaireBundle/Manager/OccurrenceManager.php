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
        $dernieresOccurences = $this->findBy(array('questionnaire' => $questionnaire, 'user' => $user), array('id' => 'DESC'), 1);

        return (count($dernieresOccurences) > 0 ? $dernieresOccurences[0] : null);
    }
    
    /**
     * Retourne la première occurrence d'un questionnaire pour un utilisateur.
     * 
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire Questionnaire
     * @param \HopitalNumerique\UserBundle\Entity\User                   $user          Utilisateur
     * @return \HopitalNumerique\QuestionnaireBundle\Entity\Occurrence|NULL Première occurrence ou NULL si aucune occurrence
     */
    public function getPremiereOccurenceByQuestionnaireAndUser(Questionnaire $questionnaire, User $user)
    {
        $premieresOccurences = $this->findBy(array('questionnaire' => $questionnaire, 'user' => $user), array('id' => 'ASC'), 1);

        return (count($premieresOccurences) > 0 ? $premieresOccurences[0] : null);
    }
    
    /**
     * Supprime les occurrences multiples d'un questionnaire d'un utilisateur (ne conserve que la première créée pour conserver les réponses).
     * 
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire Questionnaire
     * @param \HopitalNumerique\UserBundle\Entity\User                   $user          Utilisateur
     * @return void
     */
    public function deleteOccurrencesMultiplesByQuestionnaireAndUser(Questionnaire $questionnaire, User $user)
    {
        $occurrences = $user->getQuestionnaireOccurrences();
            
        if (count($occurrences) > 1)
        {
            $premiereOccurrence = $this->getPremiereOccurenceByQuestionnaireAndUser($questionnaire, $user);

            foreach ($occurrences as $occurrence)
            {
                if ($occurrence->getId() != $premiereOccurrence->getId())
                {
                    $this->delete($occurrence);
                }
            }
        }
    }
}
