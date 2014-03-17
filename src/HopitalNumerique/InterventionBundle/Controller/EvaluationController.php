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
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;

/**
 * Contrôleur des évalutions des demandes d'intervention.
 */
class EvaluationController extends Controller
{
    /**
     * Action qui affiche le formulaire de création d'une évaluation.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention à évaluer
     * @return \Symfony\Component\HttpFoundation\Response Aucune réponse
     */
    public function nouveauAction(InterventionDemande $interventionDemande)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        
        if (
            $utilisateurConnecte->getId() == $interventionDemande->getReferent()->getId()
            && ($interventionDemande->getEvaluationEtat() != null && $interventionDemande->getEvaluationEtat()->getId() == InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId())
        )
        {
            $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneById(InterventionEvaluation::getEvaluationQuestionnaireId());
            //$questionnaireReponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser($questionnaire->getId(), $utilisateurConnecte->getId(), true);
            //$themeQuestionnaire = empty($questionnaireReponses) ? 'horizontal' : 'horizontal_readonly';
            //$readOnly = (in_array('ROLE_AMBASSADEUR_7', $utilisateurConnecte->getRoles()) || !empty($questionnaireReponses));

            return $this->render('HopitalNumeriqueInterventionBundle:Evaluation:nouveau.html.twig', array(
                'interventionDemande'=> $interventionDemande,
                'etablissements' => $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findEtablissementsRattachesEtRegroupes($interventionDemande),
                'questionnaire'=> $questionnaire,
                'user' => $utilisateurConnecte,
                'optionRenderForm'=> array(
                    'themeQuestionnaire' => 'horizontal'
                )
            ));
        }

        $this->get('session')->getFlashBag()->add('danger', 'Vous n\'êtes pas autorisé à créer cette évaluation.');
        return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
    }
    
    /**
     * Action qui affiche le formulaire d'évaluation d'intervention rempli.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention évaluée
     * @return \Symfony\Component\HttpFoundation\Response La vue du formulaire
     */
    public function voirAction(InterventionDemande $interventionDemande)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        
        if (
            $interventionDemande->evaluationEtatEstEvalue()
            && (
                $utilisateurConnecte->getId() == $interventionDemande->getAmbassadeur()->getId()
                || $utilisateurConnecte->getId() == $interventionDemande->getReferent()->getId()
                || $utilisateurConnecte->getId() == $interventionDemande->getCmsi()->getId()
                || ($utilisateurConnecte->getRegion() != null && $interventionDemande->getCmsi()->getRegion() != null && $utilisateurConnecte->getRegion()->getId() == $interventionDemande->getCmsi()->getRegion()->getId())
            )
        )
        {
            $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneById(InterventionEvaluation::getEvaluationQuestionnaireId());

            return $this->render('HopitalNumeriqueInterventionBundle:Evaluation:nouveau.html.twig', array(
                'interventionDemande'=> $interventionDemande,
                'etablissements' => $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findEtablissementsRattachesEtRegroupes($interventionDemande),
                'questionnaire'=> $questionnaire,
                'optionRenderForm'=> array(
                    'themeQuestionnaire' => 'horizontal_readonly'
                )
            ));
            
            /*return $this->render(
                'HopitalNumeriqueInterventionBundle:Evaluation:voir.html.twig',
                array(
                    'interventionDemande' => $interventionDemande
                )
            );*/
        }
        
        $this->get('session')->getFlashBag()->add('danger', 'Vous n\'êtes pas autorisé à visualiser cette évaluation.');
        return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
    }
    
    /**
     * Action qui envoie une relance pour le remplissage d'un formulaire d'évaluation.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $id La demande d'intervention à évaluer
     * @return \Symfony\Component\HttpFoundation\Response Aucune réponse
     */
    public function ajaxEnvoiRelanceAction(InterventionDemande $interventionDemande)
    {
        $relanceEstEnvoyee = $this->get('hopitalnumerique_intervention.manager.intervention_evaluation')->relanceReferent($interventionDemande);
        
        return new Response($relanceEstEnvoyee ? '1' : '0');
    }
}
