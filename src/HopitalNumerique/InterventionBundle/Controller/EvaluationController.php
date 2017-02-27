<?php
/**
 * Contrôleur des évalutions des demandes d'intervention.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */

namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluation;

/**
 * Contrôleur des évalutions des demandes d'intervention.
 */
class EvaluationController extends Controller
{
    /**
     * Action qui affiche le formulaire d'évaluation d'intervention rempli.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention évaluée
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue du formulaire
     */
    public function voirAction(InterventionDemande $interventionDemande)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();

        if ($this->container->get('hopitalnumerique_intervention.manager.intervention_evaluation')->utilisateurPeutVisualiser($interventionDemande, $utilisateurConnecte)) {
            $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneById(InterventionEvaluation::getEvaluationQuestionnaireId());

            $this->container->get('hopitalnumerique_intervention.service.demande.etat_type_derniere_demande')->setDerniereDemandeOuverte($interventionDemande);

            return $this->render('HopitalNumeriqueInterventionBundle:Evaluation:voir.html.twig', [
                'interventionDemande' => $interventionDemande,
                'questionnaire' => $questionnaire,
                'optionRenderForm' => [
                    'themeQuestionnaire' => 'vertical_readonly',
                ],
            ]);
        }

        $this->get('session')->getFlashBag()->add('danger', 'Vous n\'êtes pas autorisé à visualiser cette évaluation.');

        return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
    }

    /**
     * Action qui envoie une relance pour le remplissage d'un formulaire d'évaluation.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $id La demande d'intervention à évaluer
     *
     * @return \Symfony\Component\HttpFoundation\Response Aucune réponse
     */
    public function ajaxEnvoiRelanceAction(InterventionDemande $interventionDemande)
    {
        $relanceEstEnvoyee = $this->get('hopitalnumerique_intervention.manager.intervention_evaluation')->relanceReferent($interventionDemande);

        return new Response($relanceEstEnvoyee ? '1' : '0');
    }
}
