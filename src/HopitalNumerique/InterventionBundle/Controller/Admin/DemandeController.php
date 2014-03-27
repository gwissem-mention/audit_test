<?php
/**
 * Contrôleur des demandes d'intervention pour la console administrative.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contrôleur des demandes d'intervention pour la console administrative.
 */
class DemandeController extends Controller
{
    /**
     * Action pour la visualisation d'une liste de demandes d'intervention.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste de demandes d'intervention
     */
    public function listeAction()
    {
        $gridDemandes = $this->get('hopitalnumerique_intervention.grid.admin.intervention_demandes');

        return $gridDemandes->render('HopitalNumeriqueInterventionBundle:Admin/Demande:liste.html.twig');
    }
    
    /**
     * Action pour la liste des demandes d'intervention.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des demandes d'intervention
     */
    public function gridDemandesAction()
    {
        $interventionDemandesGrille = $this->get('hopitalnumerique_intervention.grid.admin.intervention_demandes');
    
        return $interventionDemandesGrille->render('HopitalNumeriqueInterventionBundle:Grid:Admin/demandes.html.twig');
    }
}
