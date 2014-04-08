<?php
/**
 * Manager pour les évaluations des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;
use HopitalNumerique\InterventionBundle\Manager\InterventionCourrielManager;
use HopitalNumerique\UserBundle\Entity\User;

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
     * Indique si l'utilisateur peut évaluer une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention à évaluer
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateur L'utilisateur qui souhaite évaluer
     * @return boolean VRAI ssi l'utilisateur peut évaluer la demande
     */
    public function utilisateurPeutEvaluer(InterventionDemande $interventionDemande, User $utilisateur)
    {
        return (
            !$interventionDemande->estDemandeRegroupee()
            && ($interventionDemande->getEvaluationEtat() != null && $interventionDemande->evaluationEtatEstAEvaluer())
            && ($interventionDemande->getReferent()->getId() == $utilisateur->getId())
        );
    }
    /**
     * Indique si l'utilisateur peut lire une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention à lire
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateur L'utilisateur qui souhaite lire l'évaluation
     * @return boolean VRAI ssi l'utilisateur peut lire l'évaluation de la demande
     */
    public function utilisateurPeutVisualiser(InterventionDemande $interventionDemande, User $utilisateur)
    {
        return (
            !$interventionDemande->estDemandeRegroupee()
            && $interventionDemande->evaluationEtatEstEvalue()
            &&
            (
                ($interventionDemande->getReferent()->getId() == $utilisateur->getId())
                || ($interventionDemande->getDirecteur() != null && $interventionDemande->getDirecteur()->getId() == $utilisateur->getId())
                || ($interventionDemande->getAmbassadeur()->getId() == $utilisateur->getId())
                || ($interventionDemande->getCmsi()->getId() == $utilisateur->getId())
                || ($utilisateur->getRegion() != null && $interventionDemande->getCmsi()->getRegion() != null && $utilisateur->getRegion()->getId() == $interventionDemande->getCmsi()->getRegion()->getId())
            )
        );
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
