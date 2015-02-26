<?php
/**
 * Contrôleur des demandes d'intervention pour la console administrative.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller\Admin;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;

/**
 * Contrôleur des demandes d'intervention pour la console administrative.
 */
class DemandeController extends \HopitalNumerique\InterventionBundle\Controller\DemandeController
{
    /**
     * Action pour la visualisation d'une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $id La demande d'intervention à visionner
     * @return \Symfony\Component\HttpFoundation\Response La vue de la visualisation d'une demande d'intervention
     */
    public function voirAction(InterventionDemande $id)
    {
        $interventionDemande = $id;

        $this->container->get('hopitalnumerique_intervention.service.demande.etat_type_derniere_demande')->setDerniereDemandeOuverte($interventionDemande);

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Admin/Demande:voir.html.twig',
            $this->getVueParametresVoir($interventionDemande)
        );
    }
    
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
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        
        if ($this->container->get('nodevo_acl.manager.acl')->checkAuthorization($this->generateUrl('hopital_numerique_intervention_admin_demande_delete', array('id' => 0)), $utilisateurConnecte) != -1)
        {
            $interventionDemandes = $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy(array('id' => $primaryKeys));
            $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->delete($interventionDemandes);
            
            $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
        }
        else
        {
            $this->get('session')->getFlashBag()->add('warning', 'Vous ne possédez pas les droits nécessaires pour supprimer des interventions.');
        }
        
        return $this->redirect( $this->generateUrl('hopital_numerique_intervention_admin_liste'));
    }
    
    /**
     * Export de masse des demandes d'intervention.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param boolean $allPrimaryKeys 
     *
     * @return Redirect
     */
    public function exportMassAction($primaryKeys, $allPrimaryKeys)
    {
        if ($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findAll();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data->getId();
        }
        
        return $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getExportCsv($primaryKeys, $this->container->getParameter('kernel.charset'));
    }
}
