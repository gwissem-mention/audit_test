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
                    return new Response(1);
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
                        else return new Response(0);
                    }

                    $this->get('hopitalnumerique_intervention.manager.intervention_demande')->save($interventionDemande);
                    $this->_envoieCourriel($interventionDemande, $interventionEtat);
                    $this->get('session')->getFlashBag()->add('success', 'L\'état de la demande d\'intervention a été modifié.');
                    return new Response(1);
                }
            }
        }

        $this->get('session')->getFlashBag()->add('danger', 'L\'état de la demande d\'intervention n\'a pu être modifié.');
        return new Response(0);
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
    }
}
