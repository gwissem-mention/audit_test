<?php
namespace HopitalNumerique\QuestionnaireBundle\Controller;

use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;

/**
 * Contrôleur des occurrences.
 */
class OccurrenceController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Crée une occurrence et redirige vers le questionnaire.
     * 
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire Questionnaire
     */
    public function addAction(Questionnaire $questionnaire)
    {
        $user = $this->getUser();
        if (null === $user)
        {
            throw new \Exception('Aucun utilisateur n\'est connecté.');
        }

        $nouvelOccurrence = $this->container->get('hopitalnumerique_questionnaire.manager.occurrence')->createEmpty();
        $nouvelOccurrence->setUser($user);
        $nouvelOccurrence->setQuestionnaire($questionnaire);
        $this->container->get('hopitalnumerique_questionnaire.manager.occurrence')->save($nouvelOccurrence);
        
        $this->get('session')->getFlashBag()->add( 'success', 'Questionnaire créé.' );

        return $this->redirect($this->generateUrl(
            'hopitalnumerique_questionnaire_edit_front_gestionnaire_occurrence',
            array
            (
                'questionnaire' => $questionnaire->getId(),
                'occurrence' => $nouvelOccurrence->getId()
            )
        ));
    }
}
