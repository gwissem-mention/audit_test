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
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Manager pour les demandes d'intervention.
 */
class InterventionDemandeManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande';

    /**
     * @var \Symfony\Component\Security\Core\SecurityContext  SecurityContext de l'application
     */
    private $securityContext;
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router Router de l'application
     */
    private $router;
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\InterventionEtatManager Le manager de l'entité InterventionEtat
     */
    private $interventionEtatManager;
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\InterventionCourrielManager Le manager de l'entité InterventionCourriel
     */
    private $interventionCourrielManager;

    /**
     * Constructeur du manager gérant les demandes d'intervention.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router Router de l'application
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionEtatManager $interventionEtatManager Le manager de l'entité InterventionEtat
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionCourrielManager $interventionCourrielManager Le manager de l'entité InterventionCourriel
     * @return void
     */
    public function __construct(EntityManager $entityManager, SecurityContext $securityContext, Router $router, InterventionEtatManager $interventionEtatManager, InterventionCourrielManager $interventionCourrielManager)
    {
        parent::__construct($entityManager);
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->interventionEtatManager = $interventionEtatManager;
        $this->interventionCourrielManager = $interventionCourrielManager;
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
     * Envoie les relances pour les demandes d'intervention non traitées.
     * 
     * @return void
     */
    public function relanceInterventionDemandes()
    {
        $this->relanceInterventionDemandesEnAttenteCmsi();
    }
    /**
     * Envoie les relances pour les demandes d'intervention en attente CMSI non traitées.
     *
     * @return void
     */
    private function relanceInterventionDemandesEnAttenteCmsi()
    {
        $interventionDemandes = $this->_repository->findByEtatAttenteCmsiPourRelance();
        
        foreach ($interventionDemandes as $interventionDemande)
        {
            $interventionDemande->setCmsiDateDerniereRelance(new \DateTime());
            $this->save($interventionDemande);
            $this->interventionCourrielManager->envoiCourrielDemandeAcceptationCmsi($interventionDemande->getCmsi(), $this->router->generate('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
        }
    }
    
    /**
     * Retourne les données formatées pour la création du grid des nouvelles demandes d'intervention pour le CMSI.
     * 
     * @return array Les données pour le grid des nouvelles demandes d'intervention
     */
    public function getGridDonnees_CmsiDemandesNouvelles()
    {
        $interventionDemandes = $this->_repository->getGridDonnees_CmsiDemandesNouvelles($this->securityContext->getToken()->getUser());

        return $interventionDemandes;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention traitées pour le CMSI.
     * 
     * @return array Les données pour le grid des demandes d'intervention traitées
     */
    public function getGridDonnees_CmsiDemandesTraitees()
    {
        $interventionDemandes = $this->_repository->getGridDonnees_CmsiDemandesTraitees($this->securityContext->getToken()->getUser());

        return $interventionDemandes;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour l'ambassadeur.
     * 
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonnees_AmbassadeurDemandes()
    {
        $interventionDemandes = $this->_repository->getGridDonnees_AmbassadeurDemandes($this->securityContext->getToken()->getUser());

        return $interventionDemandes;
    }
}
