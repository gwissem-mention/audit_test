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
    /**
     * Suppression des demandes d'intervention de la liste.
     *
     * @param integer[] $primaryKeys Les ID des demandes d'intervention sélectionnées par l'utilisateur
     * @return \Component\HttpFoundation\RedirectResponse Redirection vers la liste
     */
    public function gridSupprimeMassAction(array $primaryKeys)
    {
        $interventionDemandes = $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy(array('id' => $primaryKeys));
        $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->delete($interventionDemandes);
        
        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
        
        return $this->redirect( $this->generateUrl('hopital_numerique_intervention_admin_liste'));
    }
}
