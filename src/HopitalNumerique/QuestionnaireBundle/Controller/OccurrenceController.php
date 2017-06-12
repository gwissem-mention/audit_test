<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Contrôleur des occurrences.
 */
class OccurrenceController extends Controller
{
    /**
     * Crée une occurrence et redirige vers le questionnaire.
     *
     * @param Questionnaire $questionnaire Questionnaire
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function addAction(Questionnaire $questionnaire)
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new \Exception('Aucun utilisateur n\'est connecté.');
        }

        $nouvelOccurrence = $this->container->get('hopitalnumerique_questionnaire.manager.occurrence')->createEmpty();
        $nouvelOccurrence->setUser($user);
        $nouvelOccurrence->setQuestionnaire($questionnaire);
        $this->container->get('hopitalnumerique_questionnaire.manager.occurrence')->save($nouvelOccurrence);

        $this->get('session')->getFlashBag()->add('success', 'Questionnaire créé.');

        return $this->redirect($this->generateUrl(
            'hopitalnumerique_questionnaire_edit_front_gestionnaire_occurrence',
            [
                'questionnaire' => $questionnaire->getId(),
                'occurrence' => $nouvelOccurrence->getId(),
            ]
        ));
    }
}
