<?php
/**
 * Contrôleur des évalutions des demandes d'intervention pour l'administration.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluation;

/**
 * Contrôleur des évalutions des demandes d'intervention pour l'administration.
 */
class EvaluationController extends Controller
{
    /**
     * Action qui affiche le formulaire d'évaluation d'intervention rempli.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention évaluée
     * @return \Symfony\Component\HttpFoundation\Response La vue du formulaire
     */
    public function voirAction(InterventionDemande $interventionDemande)
    {
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneById(InterventionEvaluation::getEvaluationQuestionnaireId());

        return $this->render('HopitalNumeriqueInterventionBundle:Admin/Evaluation:voir.html.twig', array(
            'interventionDemande'=> $interventionDemande,
            'questionnaire'=> $questionnaire,
            'optionRenderForm'=> array(
                'themeQuestionnaire' => 'vertical_readonly'
            )
        ));
    }
}
