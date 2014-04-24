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
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\InterventionBundle\Manager\InterventionEtatManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

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
     * @var \HopitalNumerique\InterventionBundle\Manager\InterventionEvaluationEtatManager Le manager de l'entité InterventionEvaluationEtat
     */
    private $interventionEvaluationEtatManager;
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\InterventionRegroupementManager Le manager de l'entité InterventionRegroupement
     */
    private $interventionRegroupementManager;
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\InterventionCourrielManager Le manager de l'entité InterventionCourriel
     */
    private $interventionCourrielManager;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User L'utilisateur connecté
     */
    private $utilisateurConnecte;
    
    
    /**
     * Constructeur du manager gérant les demandes d'intervention.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext SecurityContext de l'application
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router Router de l'application
     * @param \HopitalNumerique\UserBundle\Manager\UserManager $userManager Le manager de l'entité User
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionEtatManager $interventionEtatManager Le manager de l'entité InterventionEtat
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionRegroupementManager $interventionRegroupementManager Le manager de l'entité InterventionRegroupement
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionCourrielManager $interventionCourrielManager Le manager de l'entité InterventionCourriel
     * @return void
     */
    public function __construct(EntityManager $entityManager, SecurityContext $securityContext, Router $router, UserManager $userManager, InterventionEtatManager $interventionEtatManager, InterventionEvaluationEtatManager $interventionEvaluationEtatManager, InterventionRegroupementManager $interventionRegroupementManager, InterventionCourrielManager $interventionCourrielManager)
    {
        parent::__construct($entityManager);
        $this->securityContext                   = $securityContext;
        $this->router                            = $router;
        $this->userManager                       = $userManager;
        $this->interventionEtatManager           = $interventionEtatManager;
        $this->interventionEvaluationEtatManager = $interventionEvaluationEtatManager;
        $this->interventionRegroupementManager   = $interventionRegroupementManager;
        $this->interventionCourrielManager       = $interventionCourrielManager;
        
        $this->utilisateurConnecte = $this->securityContext->getToken()->getUser();
    }

    /**
     * Retourne la liste des interventions de l'utilisateur
     *
     * @return array
     */
    public function getForFactures( $user )
    {
        return $this->getRepository()->getForFactures( $user )->getQuery()->getResult();
    }

    /**
     * Retourne les établissements rattachés et qui n'ont pas été regroupés (pour éviter les doublons lors de l'affichage).
     *
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement\InterventionDemande $interventionDemande La demande d'intervention des établissements
     * @return \HopitalNumerique\EtablissementBundle\Entity\Etablissement[] Les établissements rattachés et non regroupés
     */
    public function findEtablissementsRattachesNonRegroupes(InterventionDemande $interventionDemande)
    {
        $etablissements = array();
        
        foreach ($interventionDemande->getEtablissements() as $etablissement)
        {
            $etablissementEstPresent = false;
            foreach ($interventionDemande->getInterventionRegroupementsDemandesRegroupees() as $interventionRegroupement)
            {
                if ($interventionRegroupement->getInterventionDemandeRegroupee()->getReferent()->getEtablissementRattachementSante() != null && $etablissement->getId() == $interventionRegroupement->getInterventionDemandeRegroupee()->getReferent()->getEtablissementRattachementSante()->getId())
                {
                    $etablissementEstPresent = true;
                    break;
                }
            }
            if (!$etablissementEstPresent)
                $etablissements[] = $etablissement;
        }
        
        return $etablissements;
    }
    /**
     * Retourne les établissements rattachés et regroupés.
     *
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement\InterventionDemande $interventionDemande La demande d'intervention des établissements
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement\InterventionRegroupement[] $interventionRegroupements Les regroupements d'intervention
     * @return \HopitalNumerique\EtablissementBundle\Entity\Etablissement[] Les établissements rattachés et non regroupés
     */
    public function findEtablissementsRattachesEtRegroupes(InterventionDemande $interventionDemande)
    {
        $etablissements = array();
        $interventionRegroupements = $this->interventionRegroupementManager->findBy(array('interventionDemandePrincipale' => $interventionDemande));

        foreach ($interventionRegroupements as $interventionRegroupement)
        {
            if ($interventionRegroupement->getInterventionDemandeRegroupee()->getReferent()->getEtablissementRattachementSante() != null)
                $etablissements[] = $interventionRegroupement->getInterventionDemandeRegroupee()->getReferent()->getEtablissementRattachementSante();
        }

        foreach ($interventionDemande->getEtablissements() as $etablissement)
        {
            $etablissementEstPresent = false;
            foreach ($etablissements as $etablissementPresent)
            {
                if ($etablissement->getId() == $etablissementPresent->getId())
                {
                    $etablissementEstPresent = true;
                    break;
                }
            }
            if (!$etablissementEstPresent)
                $etablissements[] = $etablissement;
        }
    
        return $etablissements;
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
     * Retourne si l'utilisateur connecté peut visualiser une demande d'intervention ou non.
     * 
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement\InterventionDemande $interventionDemande La demande d'intervention à visualiser
     * @return boolean VRAI ssi l'utilisateur connecté peut visualiser la demande d'intervention
     */
    public function peutVoir(InterventionDemande $interventionDemande)
    {
        return (
            ($this->utilisateurConnecte->hasRoleCmsi() && $this->utilisateurConnecte->getId() == $interventionDemande->getCmsi()->getId())
            || ($this->utilisateurConnecte->hasRoleAmbassadeur() && $this->utilisateurConnecte->getId() == $interventionDemande->getAmbassadeur()->getId())
            || ($this->utilisateurConnecte->hasRoleDirecteur() && $interventionDemande->getDirecteur() != null && $this->utilisateurConnecte->getId() == $interventionDemande->getDirecteur()->getId())
            // Établissement toujours par défaut
            || ($this->utilisateurConnecte->getId() == $interventionDemande->getReferent()->getId())
            || $this->interventionRegroupementManager->interventionRegroupementsDemandePrincipaleHaveReferent($interventionDemande, $this->utilisateurConnecte)
        );
    }
    /**
     * Retourne si l'utilisateur connecté peut éditer une demande d'intervention ou non.
     * 
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement\InterventionDemande $interventionDemande La demande d'intervention à éditer
     * @return boolean VRAI ssi l'utilisateur connecté peut éditer la demande d'intervention
     */
    public function peutEditer(InterventionDemande $interventionDemande)
    {
        return (
            ($this->utilisateurConnecte->hasRoleCmsi() && $this->utilisateurConnecte->getId() == $interventionDemande->getCmsi()->getId())
        );
    }
    /**
     * Retourne si l'utilisateur d'un établissement peut annuler cette demande d'intervention.
     *
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement\InterventionDemande $interventionDemande La demande d'intervention
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateur L'utilisateur de l'établissement qui souhaite annuler la demande
     * @return VRAI ssi l'utilisateur peut annuler la demande d'intervention
     */
    public function etablissementPeutAnnulerDemande(InterventionDemande $interventionDemande, User $utilisateur)
    {
        if (!$utilisateur->hasRoleCmsi() && !$utilisateur->hasRoleAmbassadeur())
            if (count($interventionDemande->getInterventionRegroupementsDemandesRegroupees()) == 0 && !$interventionDemande->interventionEtatEstAcceptationAmbassadeur() && !$interventionDemande->interventionEtatEstTermine() && !$interventionDemande->interventionEtatEstCloture() && !$interventionDemande->interventionEtatEstAnnuleEtablissement())
                return true;
        return false;
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
     * Envoie des simples relances (aucun enregistrement en base de données).
     * 
     * @return void
     */
    public function relanceSimple()
    {
        $this->relanceSimpleInterventionDemandesEnAttenteCmsi();
    }
    /**
     * Envoie les relances pour les demandes d'intervention en attente CMSI non traitées.
     *
     * @return void
     */
    private function relanceSimpleInterventionDemandesEnAttenteCmsi()
    {
        $interventionDemandes = $this->_repository->findBy(array('interventionEtat' => $this->interventionEtatManager->getInterventionEtatAttenteCmsi()));
        
        foreach ($interventionDemandes as $interventionDemande)
            $this->interventionCourrielManager->envoiCourrielRelanceAttenteCmsi($interventionDemande);
    }
    
    /**
     * Retourne les données formatées pour la création du grid des nouvelles demandes d'intervention pour le CMSI.
     * 
     * @return array Les données pour le grid des nouvelles demandes d'intervention
     */
    public function getGridDonneesCmsiDemandesNouvelles()
    {
        $interventionDemandes = $this->_repository->getGridDonneesCmsiDemandesNouvelles($this->utilisateurConnecte);

        return $interventionDemandes;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention traitées pour le CMSI.
     * 
     * @return array Les données pour le grid des demandes d'intervention traitées
     */
    public function getGridDonneesCmsiDemandesTraitees()
    {
        $interventionDemandes = $this->_repository->getGridDonneesCmsiDemandesTraitees($this->utilisateurConnecte);

        return $interventionDemandes;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour le directeur.
     *
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonneesDirecteurSuiviDemandes()
    {
        $interventionDemandes = $this->_repository->getGridDonneesDirecteurSuiviDemandes($this->utilisateurConnecte);
    
        return $interventionDemandes;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour l'ambassadeur.
     * 
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonneesAmbassadeurDemandes()
    {
        $interventionDemandes = $this->_repository->getGridDonneesAmbassadeurDemandes($this->utilisateurConnecte);

        return $interventionDemandes;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour l'établissement.
     *
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonneesEtablissementDemandes()
    {
        $referent = $this->utilisateurConnecte;
        $interventionDemandes = $this->_repository->getGridDonneesEtablissementDemandes($referent);
    
        return $interventionDemandes;
    }
    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour l'administration.
     *
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonneesAdminDemandes()
    {
        $interventionDemandes = $this->_repository->getGridDonneesAdminDemandes();

        return $interventionDemandes;
    }
    
    /**
     * Retourne les demandes d'intervention similaire par rapport à l'ambassadeur d'une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut rechercher les demandes similaires
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande[] Les demandes d'intervention similaires par rapport à l'ambassadeur
     */
    public function getInterventionsSimilairesParAmbassadeur(InterventionDemande $interventionDemande)
    {
        return $this->_repository->getInterventionsSimilairesParAmbassadeur($interventionDemande);
    }
    /**
     * Retourne les demandes d'intervention similaire par rapport aux objets d'une demande d'intervention.
     * 
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut rechercher les demandes similaires
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionDemande[] Les demandes d'intervention similaires par rapport aux objets
     */
    public function getInterventionsSimilairesParObjets(InterventionDemande $interventionDemande)
    {
        return $this->_repository->getInterventionsSimilairesParObjets($interventionDemande);
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
            $this->changeAmbassadeurDemandesRegroupees($interventionDemande, $nouvelAmbassadeur);
            
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
    /**
     * Change l'ambassadeur des demandes d'intervention regroupées d'une demande.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention principale des demandes regroupées dont il faut changer également l'ambassadeur
     * @param \HopitalNumerique\UserBundle\Entity\User $nouvelAmbassadeur Le nouvel ambassadeur de la demande d'intervention
     * @return void
     */
    private function changeAmbassadeurDemandesRegroupees(InterventionDemande $interventionDemande, User $nouvelAmbassadeur)
    {
        $interventionRegroupements = $this->interventionRegroupementManager->findBy(array('interventionDemandePrincipale' => $interventionDemande));
    
        foreach ($interventionRegroupements as $interventionRegroupement)
        {
            $this->changeAmbassadeur($interventionRegroupement->getInterventionDemandeRegroupee(), $nouvelAmbassadeur);
        }
    }


    /**
     * Vérifie et change l'état d'une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut modifier l'état d'intervention
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEtat Le nouvel état de la demande d'intervention
     * @param string|null $messageJustificationChangementEtat Message de justification (refus) de changement d'état
     * @return boolean VRAI ssi l'état a été modifié
     */
    public function changeEtat(InterventionDemande $interventionDemande, Reference $interventionEtat, $messageJustificationChangementEtat = null)
    {
        $this->changeEtatInterventionDemandesRegroupees($interventionDemande, $interventionEtat, $messageJustificationChangementEtat);

        if ($this->utilisateurConnecte->hasRoleCmsi() && $interventionDemande->getCmsi()->getId() == $this->utilisateurConnecte->getId())
            return $this->changeEtatPourCmsi($interventionDemande, $interventionEtat, $messageJustificationChangementEtat);
        else if ($this->utilisateurConnecte->hasRoleAmbassadeur() && $interventionDemande->getAmbassadeur()->getId() == $this->utilisateurConnecte->getId())
            return $this->changeEtatPourAmbassadeur($interventionDemande, $interventionEtat, $messageJustificationChangementEtat);
        else return $this->changeEtatPourEtablissement($interventionDemande, $interventionEtat);
    }
    /**
     * Change l'état des demandes d'intervention regroupées d'une demande.
     * 
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention principale des demandes regroupées dont il faut changer également l'état
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEtat Le nouvel état de la demande d'intervention
     * @param string|null $messageJustificationChangementEtat Message de justification (refus) de changement d'état
     * @return void
     */
    private function changeEtatInterventionDemandesRegroupees(InterventionDemande $interventionDemande, Reference $interventionEtat, $messageJustificationChangementEtat)
    {
        foreach ($interventionDemande->getInterventionRegroupementsDemandesRegroupees() as $interventionRegroupement)
            $this->changeEtat($interventionRegroupement->getInterventionDemandeRegroupee(), $interventionEtat, $messageJustificationChangementEtat);
    }
    /**
     * Vérifie et change l'état d'une demande d'intervention pour un CMSI.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut modifier l'état d'intervention
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEtat Le nouvel état de la demande d'intervention
     * @param string|null $messageJustificationChangementEtat Message de justification (refus) de changement d'état
     * @return boolean VRAI ssi l'état a été modifié
     */
    private function changeEtatPourCmsi(InterventionDemande $interventionDemande, Reference $interventionEtat, $messageJustificationChangementEtat)
    {
        if ($interventionDemande->interventionEtatEstDemandeInitiale() || $interventionDemande->interventionEtatEstAttenteCmsi())
        {
            if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatAttenteCmsiId())
            {
                $interventionDemande->setInterventionEtat($interventionEtat);
                $interventionDemande->setCmsiDateDerniereRelance(new \DateTime());
                $this->save($interventionDemande);

                return true;
            }
            else if (in_array($interventionEtat->getId(), array(InterventionEtat::getInterventionEtatAcceptationCmsiId(), InterventionEtat::getInterventionEtatRefusCmsiId())))
            {
                $interventionDemande->setInterventionEtat($interventionEtat);
                $interventionDemande->setCmsiDateChoix(new \DateTime());
                if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatRefusCmsiId())
                {
                    if ($messageJustificationChangementEtat != null)
                        $interventionDemande->setRefusMessage($messageJustificationChangementEtat);
                }

                $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
                $this->save($interventionDemande);

                if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatRefusCmsiId())
                    $this->interventionCourrielManager->envoiCourrielEstRefuseCmsi($interventionDemande->getReferent(), $this->router->generate('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
                else if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationCmsiId())
                    $this->interventionCourrielManager->envoiCourrielDemandeAcceptationAmbassadeur($interventionDemande->getAmbassadeur(), $this->router->generate('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));

                return true;
            }
        }
        return false;
    }
    /**
     * Vérifie et change l'état d'une demande d'intervention pour un ambassadeur.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut modifier l'état d'intervention
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEtat Le nouvel état de la demande d'intervention
     * @param string|null $messageJustificationChangementEtat Message de justification (refus) de changement d'état
     * @return boolean VRAI ssi l'état a été modifié
     */
    private function changeEtatPourAmbassadeur(InterventionDemande $interventionDemande, Reference $interventionEtat, $messageJustificationChangementEtat)
    {
        if ($interventionDemande->interventionEtatEstAcceptationCmsi())
        {
            if (in_array($interventionEtat->getId(), array(InterventionEtat::getInterventionEtatAcceptationAmbassadeurId(), InterventionEtat::getInterventionEtatRefusAmbassadeurId())))
            {
                $interventionDemande->setInterventionEtat($interventionEtat);
                $interventionDemande->setAmbassadeurDateChoix(new \DateTime());
                if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatRefusAmbassadeurId())
                {
                    if ($messageJustificationChangementEtat != null)
                        $interventionDemande->setRefusMessage($messageJustificationChangementEtat);
                }
                else if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationAmbassadeurId())
                {
                    $interventionDemande->setEvaluationEtat($this->interventionEvaluationEtatManager->getInterventionEvaluationEtatAEvaluer());
                }

                $this->save($interventionDemande);
                
                if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatRefusAmbassadeurId())
                    $this->interventionCourrielManager->envoiCourrielEstRefuseAmbassadeur($interventionDemande->getCmsi(), $interventionDemande->getReferent(), $this->router->generate('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
                elseif ($interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationAmbassadeurId())
                    $this->interventionCourrielManager->envoiCourrielEstAccepteAmbassadeur($interventionDemande->getCmsi(), $interventionDemande->getReferent(), $this->router->generate('hopital_numerique_intervention_demande_voir', array('id' => $interventionDemande->getId()), true));
                
                return true;
            }
        }

        return false;
    }
    /**
     * Vérifie et change l'état d'une demande d'intervention pour Annulé.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut modifier l'état d'intervention
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEtat Le nouvel état de la demande d'intervention
     * @return boolean VRAI ssi l'état a été modifié
     */
    private function changeEtatPourEtablissement(InterventionDemande $interventionDemande, Reference $interventionEtat)
    {
        if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatTermineId())
        {
            $interventionDemande->setInterventionEtat($interventionEtat);
            $this->save($interventionDemande);
        }
        else if ($this->etablissementPeutAnnulerDemande($interventionDemande, $this->utilisateurConnecte) && $interventionEtat->getId() == InterventionEtat::getInterventionEtatAnnulationEtablissementId())
        {
            $interventionDemande->setInterventionEtat($interventionEtat);
            $this->save($interventionDemande);

            $this->interventionCourrielManager->envoiCourrielEstAnnuleEtablissement($interventionDemande);

            return true;
        }
        return false;
    }


    /**
     * Vérifie et change l'état d'une évaluation de demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention dont il faut modifier l'état de l'évaluation
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEvaluationEtat Le nouvel état de l'évaluation
     * @return boolean VRAI ssi l'état a été modifié
     */
    public function changeEvaluationEtat(InterventionDemande $interventionDemande, Reference $interventionEvaluationEtat)
    {
        $this->changeEvaluationEtatInterventionDemandesRegroupees($interventionDemande, $interventionEvaluationEtat);

        $interventionDemande->setEvaluationEtat($interventionEvaluationEtat);
        $this->save($interventionDemande);
    }
    /**
     * Change l'état dévaluation des demandes d'intervention regroupées d'une demande.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention principale des demandes regroupées dont il faut changer également l'état d'évaluation
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEvaluationEtat Le nouvel état de l'évaluation
     * @return void
     */
    private function changeEvaluationEtatInterventionDemandesRegroupees(InterventionDemande $interventionDemande, Reference $interventionEvaluationEtat)
    {
        foreach ($interventionDemande->getInterventionRegroupementsDemandesRegroupees() as $interventionRegroupement)
            $this->changeEvaluationEtat($interventionRegroupement->getInterventionDemandeRegroupee(), $interventionEvaluationEtat);
    }
    
}
