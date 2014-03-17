<?php
/**
 * Contrôleur des états des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * Contrôleur des demandes d'intervention.
 */
class EtatController extends Controller
{
    /**
     * Action pour la modification de l'état d'une demande d'intervention.
     * 
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut modifier l'état d'intervention
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEtat Le nouvel état de la demande d'intervention
     * @return \Symfony\Component\HttpFoundation\Response 1 ssi le nouvel état est valide
     */
    public function ajaxChangeAction(InterventionDemande $interventionDemande, Reference $interventionEtat)
    {
        if ($this->_changeEtatPourCmsi($interventionDemande, $interventionEtat) || $this->_changeEtatPourAmbassadeur($interventionDemande, $interventionEtat))
            return new Response(1);

        $this->get('session')->getFlashBag()->add('danger', 'L\'état de la demande d\'intervention n\'a pu être modifié.');
        return new Response(0);
    }
    
    /**
     * Vérifie et change l'état d'une demande d'intervention pour un CMSI.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut modifier l'état d'intervention
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEtat Le nouvel état de la demande d'intervention
     * @return boolean VRAI ssi l'état a été modifié
     */
    private function _changeEtatPourCmsi(InterventionDemande $interventionDemande, Reference $interventionEtat)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        
        if ($utilisateurConnecte->hasRoleCmsi())
        {
            if ($interventionDemande->interventionEtatEstDemandeInitiale() || $interventionDemande->interventionEtatEstAttenteCmsi())
            {
                if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatAttenteCmsiId())
                {
                    $interventionDemande->setInterventionEtat($interventionEtat);
                    $interventionDemande->setCmsiDateDerniereRelance(new \DateTime());
                    $this->get('hopitalnumerique_intervention.manager.intervention_demande')->save($interventionDemande);
        
                    $this->get('session')->getFlashBag()->add('success', 'L\'état de la demande d\'intervention a été mis en attente.');
                    return true;
                }
                else if (in_array($interventionEtat->getId(), array(InterventionEtat::getInterventionEtatAcceptationCmsiId(), InterventionEtat::getInterventionEtatRefusCmsiId())))
                {
                    $interventionDemande->setInterventionEtat($interventionEtat);
                    $interventionDemande->setCmsiDateChoix(new \DateTime());
                    if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatRefusCmsiId())
                    {
                        if ($this->get('request')->isMethod('POST'))
                        {
                            $messageRefus = ($this->get('request')->request->get('message') != '' ? $this->get('request')->request->get('message') : null);
                            $interventionDemande->setRefusMessage($messageRefus);
                        }
                        else return false;
                    }
        
                    $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
                    $this->get('hopitalnumerique_intervention.manager.intervention_demande')->save($interventionDemande);
                    $this->_envoieCourriel($interventionDemande, $interventionEtat);
                    $this->get('session')->getFlashBag()->add('success', 'L\'état de la demande d\'intervention a été modifié.');
                    return true;
                }
            }
        }
        
        return false;
    }
    /**
     * Vérifie et change l'état d'une demande d'intervention pour un ambassadeur.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut modifier l'état d'intervention
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEtat Le nouvel état de la demande d'intervention
     * @return boolean VRAI ssi l'état a été modifié
     */
    private function _changeEtatPourAmbassadeur(InterventionDemande $interventionDemande, Reference $interventionEtat)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
    
        if ($utilisateurConnecte->hasRoleAmbassadeur())
        {
            if ($interventionDemande->interventionEtatEstAcceptationCmsi())
            {
                if (in_array($interventionEtat->getId(), array(InterventionEtat::getInterventionEtatAcceptationAmbassadeurId(), InterventionEtat::getInterventionEtatRefusAmbassadeurId())))
                {
                    $interventionDemande->setInterventionEtat($interventionEtat);
                    $interventionDemande->setAmbassadeurDateChoix(new \DateTime());
                    if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatRefusAmbassadeurId())
                    {
                        if ($this->get('request')->isMethod('POST'))
                        {
                            $messageRefus = ($this->get('request')->request->get('message') != '' ? $this->get('request')->request->get('message') : null);
                            $interventionDemande->setRefusMessage($messageRefus);
                        }
                        else return false;
                    }
                    else if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationAmbassadeurId())
                    {
                        $interventionDemande->setEvaluationEtat($this->get('hopitalnumerique_intervention.manager.intervention_evaluation_etat')->getInterventionEvaluationEtatAEvaluer());
                    }
    
                    $this->get('hopitalnumerique_intervention.manager.intervention_demande')->save($interventionDemande);
                    $this->_envoieCourriel($interventionDemande, $interventionEtat);
                    $this->get('session')->getFlashBag()->add('success', 'L\'état de la demande d\'intervention a été modifié.');
                    return true;
                }
            }
        }
    
        return false;
    }
    
    /**
     * Envoie le bon courriel selon le nouvel état d'intervention.
     * 
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont l'état d'intervention change
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $nouvelInterventionEtat Le nouvel état de la demande d'intervention
     * @return void
     */
    private function _envoieCourriel(InterventionDemande $interventionDemande, Reference $nouvelInterventionEtat)
    {
        if ($nouvelInterventionEtat->getId() == InterventionEtat::getInterventionEtatRefusCmsiId())
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielEstRefuseCmsi($interventionDemande->getReferent(), $this->generateUrl('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
        else if ($nouvelInterventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationCmsiId())
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielDemandeAcceptationAmbassadeur($interventionDemande->getAmbassadeur(), $this->generateUrl('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
        elseif ($nouvelInterventionEtat->getId() == InterventionEtat::getInterventionEtatRefusAmbassadeurId())
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielEstRefuseAmbassadeur($interventionDemande->getCmsi(), $interventionDemande->getReferent(), $this->generateUrl('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
        elseif ($nouvelInterventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationAmbassadeurId())
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielEstAccepteAmbassadeur($interventionDemande->getCmsi(), $interventionDemande->getReferent(), $this->generateUrl('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
    }
}
