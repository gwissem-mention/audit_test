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
    	if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
    	{
            $messageJustificationChangementEtat = ($this->get('request')->query->get('message') != '' ? $this->get('request')->query->get('message') : null);
            
            if ($this->get('hopitalnumerique_intervention.manager.intervention_demande')->changeEtat($interventionDemande, $interventionEtat, $messageJustificationChangementEtat))
            {
                $this->get('session')->getFlashBag()->add('success', 'L\'état de la demande d\'intervention a été modifié.');
                return new Response(1);
            }
    	}
    	return new Response(0);
    }
}
