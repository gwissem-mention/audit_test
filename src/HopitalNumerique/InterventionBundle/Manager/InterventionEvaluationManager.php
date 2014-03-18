<?php
/**
 * Manager pour les évaluations des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;
use HopitalNumerique\InterventionBundle\Manager\InterventionCourrielManager;

/**
 * Manager pour les évaluations des demandes d'intervention.
 */
class InterventionEvaluationManager
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext SecurityContext de l'application
     */
    private $securityContext;
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router Router de l'application
     */
    private $router;
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\InterventionCourrielManager Le manager de l'entité InterventionCourriel
     */
    private $interventionCourrielManager;
    /**
     * @var \HopitalNumerique\UserBundle\Entity\User L'utilisateur connecté
     */
    private $utilisateurConnecte;

    /**
     * Constructeur du manager gérant les évaluations des demandes d'intervention.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router Router de l'application
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionCourrielManager $interventionCourrielManager Le manager de l'entité InterventionCourriel
     * @return void
     */
    public function __construct(SecurityContext $securityContext, Router $router, InterventionCourrielManager $interventionCourrielManager)
    {
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->interventionCourrielManager = $interventionCourrielManager;
        
        $this->utilisateurConnecte = $this->securityContext->getToken()->getUser();
    }

    /**
     * Envoie une relance au référent pour remplir l'évaluation de la demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention à évaluer
     * @return boolean VRAI ssi la relance a été validée et envoyée
     */
    public function relanceReferent(InterventionDemande $interventionDemande)
    {
        if ($this->utilisateurConnecte->hasRoleAmbassadeur())
        {
            if ($interventionDemande->getEvaluationEtat()->getId() == InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId())
            {
                $this->interventionCourrielManager->envoiCourrielInvitationEvaluationReferent($interventionDemande->getReferent(), $this->router->generate('hopital_numerique_intervention_evaluation_nouveau', array('interventionDemande' => $interventionDemande->getId()), true));
                return true;
            }
        }
        
        return false;
    }
}
