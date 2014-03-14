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
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Manager\InterventionEtatManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use HopitalNumerique\UserBundle\Entity\User;

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
     * @var \HopitalNumerique\UserBundle\Manager\UserManager Le manager de l'entité User
     */
    private $userManager;
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
    public function __construct(EntityManager $entityManager, SecurityContext $securityContext, Router $router, UserManager $userManager, InterventionEtatManager $interventionEtatManager, InterventionCourrielManager $interventionCourrielManager)
    {
        parent::__construct($entityManager);
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->userManager = $userManager;
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
        $this->relanceInterventionDemandesAcceptationCmsi();
        $this->relanceInterventionDemandesRelanceAmbassadeur1();
        $this->relanceInterventionDemandesRelanceAmbassadeur2();
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
     * Envoie les relances pour les demandes d'intervention acceptées par le CMSI non traitées.
     *
     * @return void
     */
    private function relanceInterventionDemandesAcceptationCmsi()
    {
        $interventionDemandes = $this->_repository->findByEtatAcceptationCmsiPourRelance();
        
        foreach ($interventionDemandes as $interventionDemande)
        {
            $interventionDemande->setInterventionEtat($this->interventionEtatManager->getInterventionEtatAcceptationCmsiRelance1());
            $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
            $this->save($interventionDemande);
            $this->interventionCourrielManager->envoiCourrielRelanceAmbassadeur1($interventionDemande->getAmbassadeur(), $this->router->generate('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
        }
    }
    /**
     * Envoie les relances pour les demandes d'intervention non traitées en relance ambassadeur 1.
     *
     * @return void
     */
    private function relanceInterventionDemandesRelanceAmbassadeur1()
    {
        $interventionDemandes = $this->_repository->findByEtatRelanceAmbassadeur1PourRelance();
    
        foreach ($interventionDemandes as $interventionDemande)
        {
            $interventionDemande->setInterventionEtat($this->interventionEtatManager->getInterventionEtatAcceptationCmsiRelance2());
            $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
            $this->save($interventionDemande);
            $this->interventionCourrielManager->envoiCourrielRelanceAmbassadeur2($interventionDemande->getAmbassadeur(), $this->router->generate('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
        }
    }
    /**
     * Envoie les relances pour les demandes d'intervention non traitées en relance ambassadeur 2.
     *
     * @return void
     */
    private function relanceInterventionDemandesRelanceAmbassadeur2()
    {
        $interventionDemandes = $this->_repository->findByEtatRelanceAmbassadeur2PourRelance();
    
        foreach ($interventionDemandes as $interventionDemande)
        {
            $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
            $interventionDemande->setInterventionEtat($this->interventionEtatManager->getInterventionEtatCloture());
            $this->save($interventionDemande);
            $this->interventionCourrielManager->envoiCourrielRelanceAmbassadeurCloture($interventionDemande->getCmsi(), $interventionDemande->getAmbassadeur(), $interventionDemande->getReferent(), $this->router->generate('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
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
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour l'établissement.
     *
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonnees_EtablissementDemandes()
    {
        $referent = $this->securityContext->getToken()->getUser();
        $cmsiRegion = null;
        if ($referent->getRegion() != null)
            $cmsiRegion = $this->userManager->getCmsi(array('region' => $referent->getRegion(), 'enabled' => true));

        $interventionDemandes = $this->_repository->getGridDonnees_EtablissementDemandes($referent, $cmsiRegion);
    
        return $interventionDemandes;
    }

    /**
     * Change l'ambassadeur d'une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention qui change d'ambassadeur
     * @param \HopitalNumerique\UserBundle\Entity\User $nouvelAmbassadeur Le nouvelle ambassadeur de la demande
     * @return boolean VRAI ssi le nouvel ambassadeur est validé et enregistré
     */
    public function changeAmbassadeur(InterventionDemande $interventionDemande, User $nouvelAmbassadeur)
    {
        $ancienAmbassadeur = $interventionDemande->getAmbassadeur();
        
        if ($nouvelAmbassadeur->getId() != $ancienAmbassadeur->getId())
        {
            if (!$interventionDemande->haveAncienAmbassadeur($ancienAmbassadeur))
                $interventionDemande->addAncienAmbassadeur($ancienAmbassadeur);
            $interventionDemande->setAmbassadeur($nouvelAmbassadeur);
            $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
            $this->save($interventionDemande);
            
            $this->interventionCourrielManager->envoiCourrielChangementAmbassadeur(
                array(
                    $interventionDemande->getCmsi(),
                    $interventionDemande->getReferent(),
                    $nouvelAmbassadeur
                ),
                $nouvelAmbassadeur,
                $this->router->generate('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true)
            );

            return true;
        }
        
        return false;
    }
}
