<?php
/**
 * Contrôleur des évalutions des demandes d'intervention.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */

namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @param InterventionDemande $interventionDemande La demande d'intervention évaluée
     *
     * @return Response La vue du formulaire
     */
    public function voirAction(InterventionDemande $interventionDemande)
    {
        $utilisateurConnecte = $this->getUser();

        if ($this->get('hopitalnumerique_intervention.manager.intervention_evaluation')->utilisateurPeutVisualiser(
            $interventionDemande,
            $utilisateurConnecte
        )) {
            $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneById(
                InterventionEvaluation::getEvaluationQuestionnaireId()
            );

            $this->get('hopitalnumerique_intervention.service.demande.etat_type_derniere_demande')
                ->setDerniereDemandeOuverte($interventionDemande)
            ;

            return $this->render(
                'HopitalNumeriqueInterventionBundle:Evaluation:voir.html.twig',
                [
                    'interventionDemande' => $interventionDemande,
                    'questionnaire'       => $questionnaire,
                    'optionRenderForm'    => [
                        'themeQuestionnaire' => 'vertical_readonly',
                    ],
                ]
            );
        }

        $this->addFlash('danger', 'Vous n\'êtes pas autorisé à visualiser cette évaluation.');

        return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
    }

    /**
     * Action qui envoie une relance pour le remplissage d'un formulaire d'évaluation.
     *
     * @param InterventionDemande $interventionDemande
     *
     * @return Response Aucune réponse
     */
    public function ajaxEnvoiRelanceAction(InterventionDemande $interventionDemande)
    {
        $relanceEstEnvoyee = $this->get('hopitalnumerique_intervention.manager.intervention_evaluation')
            ->relanceReferent($interventionDemande)
        ;

        return new Response($relanceEstEnvoyee ? '1' : '0');
    }

    /**
     * @param Request             $request
     * @param InterventionDemande $interventionDemande
     *
     * @return RedirectResponse
     */
    public function relaunchAction(Request $request, InterventionDemande $interventionDemande)
    {
        $relaunch = $this->get('hopitalnumerique_intervention.manager.intervention_evaluation')->relanceReferent(
            $interventionDemande
        );

        if ($relaunch) {
            $this->addFlash('success', 'La relance a bien été envoyée');
        } else {
            $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi de la relance');
        }

        return $this->redirect($request->headers->get('referer'));
    }
}
