<?php
/**
 * Manager pour les demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;

use Symfony\Component\Security\Core\SecurityContext;
use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\InterventionBundle\Manager\InterventionEtatManager;

/**
 * Manager pour les demandes d'intervention.
 */
class InterventionDemandeManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande';

    /**
     * @var \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     */
    private $securityContext;
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\InterventionEtatManager Le manager de l'entité InterventionEtat
     */
    private $interventionEtatManager;

    /**
     * Constructeur du manager gérant les demandes d'intervention.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionEtatManager $interventionEtatManager Le manager de l'entité InterventionEtat
     * @return void
     */
    public function __construct(EntityManager $entityManager, SecurityContext $securityContext, InterventionEtatManager $interventionEtatManager)
    {
        parent::__construct($entityManager);
        $this->securityContext = $securityContext;
        $this->interventionEtatManager = $interventionEtatManager;
    }

    /**
     * Met à jour automatiquement les états des demandes d'intervention et envoie éventuellement les courriels adéquats.
     *
     * @return void
     */
    public function majInterventionEtatsDesInterventionDemandes()
    {
        $this->majInterventionEtatsDesInterventionDemandesEnEtatDemandeInitiale();
    }
    /**
     * Met à jour automatiquement les états des demandes d'intervention si leur état est Demande initiale.
     *
     * @return void
     */
    private function majInterventionEtatsDesInterventionDemandesEnEtatDemandeInitiale()
    {
        $interventionDemandes = $this->_repository->findByDemandesInitialesAValiderCmsi();
        
        foreach ($interventionDemandes as $interventionDemande)
        {
            $interventionDemande->setInterventionEtat($this->interventionEtatManager->getInterventionEtatAcceptationCmsi());
            $this->save($interventionDemande);
        }
    }
    
    /**
     * Retourne les données formatées pour la création du grid des nouvelles demandes d'intervention.
     * 
     * @return array Les données pour le grid des nouvelles demandes d'intervention
     */
    public function getGridDonnees_DemandesNouvelles()
    {
        $interventionDemandes = $this->_repository->getGridDonnees_DemandesNouvelles($this->securityContext->getToken()->getUser());

        return $interventionDemandes;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention traitées.
     * 
     * @return array Les données pour le grid des demandes d'intervention traitées
     */
    public function getGridDonnees_DemandesTraitees()
    {
        $interventionDemandes = $this->_repository->getGridDonnees_DemandesTraitees($this->securityContext->getToken()->getUser());

        return $interventionDemandes;
    }
}
