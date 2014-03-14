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
            
            
            //On récupère l'utilisateur qui est connecté
            $user = $this->get('security.context')->getToken()->getUser();
            
            //Récupération du questionnaire de l'expert
            $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneById(InterventionEvaluation::getEvaluationQuestionnaireId());
            
            //Récupération des réponses pour le questionnaire et utilisateur courant, triées par idQuestion en clé
            $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser($questionnaire->getId(), $utilisateurConnecte->getId(), true);
            
            $themeQuestionnaire = empty($reponses) ? 'horizontal' : 'horizontal_readonly';
            //readonly si il y a des réponses dans le questionnaire ou que le role courant de l'utilisateur est ambassadeur
            $readOnly = (in_array('ROLE_AMBASSADEUR_7', $utilisateurConnecte->getRoles()) || !empty($reponses));
            
            return $this->render('HopitalNumeriqueInterventionBundle:Evaluation:nouveau.html.twig',array(
                    'questionnaire'      => $questionnaire,
                    'user'               => $utilisateurConnecte,
                    'optionRenderForm'   => array(
                            'readOnly'           => $readOnly,
                            'themeQuestionnaire' => $themeQuestionnaire,
                            'routeRedirect'      => json_encode(array(
                                    'quit' => array(
                                            'route'     => 'hopitalnumerique_user_ambassadeur_front_edit',
                                            'arguments' => array()
                                    )
                            ))
                    )
            ));
            
            
            
            
            
            
            
            return $this->render(
                    'HopitalNumeriqueInterventionBundle:Evaluation:nouveau.html.twig',
                    array(
                            'interventionDemande' => $interventionDemande
                    )
            );
            
            
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
            $utilisateurConnecte->getId() == $interventionDemande->getAmbassadeur()->getId()
            || $utilisateurConnecte->getId() == $interventionDemande->getReferent()->getId()
            || ($utilisateurConnecte->getRegion() != null && $interventionDemande->getCmsi()->getRegion() != null && $utilisateurConnecte->getRegion()->getId() == $interventionDemande->getCmsi()->getRegion()->getId())
        )
        {
            return $this->render(
                'HopitalNumeriqueInterventionBundle:Evaluation:voir.html.twig',
                array(
                    'interventionDemande' => $interventionDemande
                )
            );
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
