<?php
/**
 * Contrôleur des regroupements d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionRegroupementType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur des regroupements d'intervention.
 */
class RegroupementController extends Controller
{
    /**
     * Action pour la visualisation d'une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemandePrincipale La demande d'intervention à qui on ajoute un établissement
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemandeRegroupee La demande d'intervention qui est regroupée à l'intervention principale
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionRegroupementType $interventionRegroupementType Le type de regroupement
     * @return \Symfony\Component\HttpFoundation\Response 1 ssi le regroupement est valide et effectué
     */
    public function ajaxRegroupeAction(InterventionDemande $interventionDemandePrincipale, InterventionDemande $interventionDemandeRegroupee, InterventionRegroupementType $interventionRegroupementType)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        
        if ($this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->utilisateurPeutRegrouperDemande($interventionDemandePrincipale, $interventionDemandeRegroupee, $utilisateurConnecte))
        {
            $interventionRegroupement = $this->get('hopitalnumerique_intervention.manager.intervention_regroupement')->createEmpty();
            $interventionRegroupement->setInterventionDemandePrincipale($interventionDemandePrincipale);
            $interventionRegroupement->setInterventionDemandeRegroupee($interventionDemandeRegroupee);
            $interventionRegroupement->setInterventionRegroupementType($interventionRegroupementType);
            $this->get('hopitalnumerique_intervention.manager.intervention_regroupement')->save($interventionRegroupement);
            
            $this->get('session')->getFlashBag()->add('success', 'Le regroupement a été réalisé.');
            
            return new Response(1);
        }
        
        return new Response(0);
    }
}