<?php
/**
 * Contrôleur des formulaires d'évaluation des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller\Admin\Form;

use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluation;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;

/**
 * Contrôleur des formulaires d'évaluation des demandes d'intervention dans l'admin.
 */
class EvaluationController extends \HopitalNumerique\InterventionBundle\Controller\Form\EvaluationController
{
    /**
     * Action qui affiche le formulaire de création d'une évaluation.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention à évaluer
     * @return \Symfony\Component\HttpFoundation\Response Aucune réponse
     */
    public function nouveauAction(InterventionDemande $interventionDemande)
    {
        $this->utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
    
        if ($this->container->get('hopitalnumerique_intervention.manager.intervention_evaluation')->utilisateurPeutEvaluer($interventionDemande, $this->utilisateurConnecte))
        {
            $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneById(InterventionEvaluation::getEvaluationQuestionnaireId());
    
            return $this->render('HopitalNumeriqueInterventionBundle:Admin/Evaluation/Form:nouveau.html.twig', array(
                'interventionDemande'=> $interventionDemande,
                'etablissements' => $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findEtablissementsRattachesEtRegroupes($interventionDemande),
                'questionnaire'=> $questionnaire,
                'user' => $this->utilisateurConnecte,
                'optionRenderForm'=> array(
                    'themeQuestionnaire' => 'vertical'
                )
            ));
        }
    
        $this->get('session')->getFlashBag()->add('danger', 'Vous n\'êtes pas autorisé à créer cette évaluation.');
        return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
    }

    /**
     * Génération dynamique du questionnaire en chargeant les réponses de l'utilisateur passés en param, ajout d'une route de redirection quand tout s'est bien passé
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande Demande d'intervention de l'évaluation à afficher
     * @return \Symfony\Component\HttpFoundation\Response Vue du formulaire d'évaluation
     */
    public function formAction(InterventionDemande $interventionDemande)
    {
        $this->routeRedirectionSucces = 'hopital_numerique_intervention_admin_liste';
        $this->utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
    
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneById(InterventionEvaluation::getEvaluationQuestionnaireId());
    
        return $this->renderForm(
            array(
                'interventionDemande' => $interventionDemande,
                'questionnaire' => $questionnaire
            ),
            'HopitalNumeriqueInterventionBundle:Admin/Evaluation/Form:form.html.twig'
        );
    }
}
