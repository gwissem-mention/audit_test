<?php
/**
 * Manager pour les demandes d'intervention.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */

namespace HopitalNumerique\InterventionBundle\Manager;

use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\InterventionBundle\Entity\InterventionRegroupement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluation;
use HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager;
use HopitalNumerique\QuestionnaireBundle\Manager\ReponseManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;

/**
 * Manager pour les demandes d'intervention.
 */
class InterventionDemandeManager extends BaseManager
{
    protected $class = 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande';

    /**
     * @var SecurityContext SecurityContext de l'application
     */
    private $securityContext;
    /**
     * @var Router Router de l'application
     */
    private $router;
    /**
     * @var InterventionEtatManager Le manager de l'entité InterventionEtat
     */
    private $interventionEtatManager;
    /**
     * @var InterventionEvaluationEtatManager Le manager de l'entité InterventionEvaluationEtat
     */
    private $interventionEvaluationEtatManager;
    /**
     * @var InterventionRegroupementManager Le manager de l'entité InterventionRegroupement
     */
    private $interventionRegroupementManager;
    /**
     * @var InterventionCourrielManager Le manager de l'entité InterventionCourriel
     */
    private $interventionCourrielManager;
    /**
     * @var QuestionnaireManager
     */
    private $questionnaireManager;
    /**
     * @var ReponseManager
     */
    private $reponseManager;
    /**
     * @var ObjetManager
     */
    private $objetManager;

    /**
     * @var User L'utilisateur connecté
     */
    private $utilisateurConnecte;

    /**
     * Constructeur du manager gérant les demandes d'intervention.
     *
     * @param EntityManager                     $entityManager
     * @param SecurityContext                   $securityContext
     * @param Router                            $router
     * @param InterventionEtatManager           $interventionEtatManager
     * @param InterventionEvaluationEtatManager $interventionEvaluationEtatManager
     * @param InterventionRegroupementManager   $interventionRegroupementManager
     * @param InterventionCourrielManager       $interventionCourrielManager
     * @param QuestionnaireManager              $questionnaireManager
     * @param ReponseManager                    $reponseManager
     * @param ObjetManager                      $objetManager
     */
    public function __construct(
        EntityManager $entityManager,
        SecurityContext $securityContext,
        Router $router,
        InterventionEtatManager $interventionEtatManager,
        InterventionEvaluationEtatManager $interventionEvaluationEtatManager,
        InterventionRegroupementManager $interventionRegroupementManager,
        InterventionCourrielManager $interventionCourrielManager,
        QuestionnaireManager $questionnaireManager,
        ReponseManager $reponseManager,
        ObjetManager $objetManager
    ) {
        parent::__construct($entityManager);
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->interventionEtatManager = $interventionEtatManager;
        $this->interventionEvaluationEtatManager = $interventionEvaluationEtatManager;
        $this->interventionRegroupementManager = $interventionRegroupementManager;
        $this->interventionCourrielManager = $interventionCourrielManager;
        $this->questionnaireManager = $questionnaireManager;
        $this->reponseManager = $reponseManager;
        $this->objetManager = $objetManager;

        $this->utilisateurConnecte = $this->securityContext->getToken()->getUser();
    }

    /**
     * Retourne la liste des interventions de l'utilisateur.
     *
     * @param User $user L'utilisateur concerné
     *
     * @return array
     */
    public function getForFactures($user = null)
    {
        return $this->getRepository()->getForFactures($user)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des interventions de l'utilisateur.
     *
     * @param User $user L'utilisateur concerné
     *
     * @return array
     */
    public function getForTotal($user)
    {
        return $this->getRepository()->getForTotal($user)->getQuery()->getResult();
    }

    /**
     * Retourne les établissements rattachés et qui n'ont pas été regroupés
     * (pour éviter les doublons lors de l'affichage).
     *
     * @param InterventionDemande $interventionDemande
     *
     * @return Etablissement[]
     */
    public function findEtablissementsRattachesNonRegroupes(InterventionDemande $interventionDemande)
    {
        $etablissements = [];

        foreach ($interventionDemande->getEtablissements() as $etablissement) {
            $etablissementEstPresent = false;
            /** @var InterventionRegroupement $interventionRegroupement */
            foreach ($interventionDemande->getInterventionRegroupementsDemandesRegroupees() as $interventionRegroupement) {
                if ($interventionRegroupement->getInterventionDemandeRegroupee()->getReferent()
                        ->getOrganization() != null
                    && $etablissement->getId() == $interventionRegroupement->getInterventionDemandeRegroupee()
                        ->getReferent()->getOrganization()->getId()
                ) {
                    $etablissementEstPresent = true;
                    break;
                }
            }
            if (!$etablissementEstPresent) {
                $etablissements[] = $etablissement;
            }
        }

        return $etablissements;
    }

    /**
     * Retourne les établissements rattachés et regroupés.
     *
     * @param InterventionDemande $interventionDemande La demande d'intervention des établissements
     *
     * @return Etablissement[] Les établissements rattachés et non regroupés
     */
    public function findEtablissementsRattachesEtRegroupes(InterventionDemande $interventionDemande)
    {
        $etablissements = [];
        $interventionRegroupements =
            $this->interventionRegroupementManager->findBy(['interventionDemandePrincipale' => $interventionDemande])
        ;

        /** @var InterventionRegroupement $interventionRegroupement */
        foreach ($interventionRegroupements as $interventionRegroupement) {
            if ($interventionRegroupement->getInterventionDemandeRegroupee()->getReferent()->getOrganization()
                != null
            ) {
                $etablissements[] = $interventionRegroupement->getInterventionDemandeRegroupee()->getReferent()
                                                             ->getOrganization();
            }
        }

        foreach ($interventionDemande->getEtablissements() as $etablissement) {
            $etablissementEstPresent = false;
            foreach ($etablissements as $etablissementPresent) {
                if ($etablissement->getId() == $etablissementPresent->getId()) {
                    $etablissementEstPresent = true;
                    break;
                }
            }
            if (!$etablissementEstPresent) {
                $etablissements[] = $etablissement;
            }
        }

        return $etablissements;
    }

    /**
     * Met à jour automatiquement les états des demandes d'intervention et envoie éventuellement les courriels adéquats.
     */
    public function majInterventionEtatsDesInterventionDemandes()
    {
        $this->majInterventionEtatsDesInterventionDemandesEnEtatDemandeInitiale();
    }

    /**
     * Met à jour automatiquement les états des demandes d'intervention si leur état est Demande initiale.
     */
    private function majInterventionEtatsDesInterventionDemandesEnEtatDemandeInitiale()
    {
        $interventionDemandes = $this->repository->findByDemandesInitialesAValiderCmsi();

        foreach ($interventionDemandes as $interventionDemande) {
            $interventionDemande->setInterventionEtat(
                $this->interventionEtatManager->getInterventionEtatAcceptationCmsi()
            );
            $this->save($interventionDemande);
        }
    }

    /**
     * Retourne si l'utilisateur connecté peut visualiser une demande d'intervention ou non.
     *
     * @param InterventionDemande $interventionDemande La demande d'intervention à visualiser
     *
     * @return bool VRAI ssi l'utilisateur connecté peut visualiser la demande d'intervention
     */
    public function peutVoir(InterventionDemande $interventionDemande)
    {
        return ($this->utilisateurConnecte->hasRoleCmsi()
           && $this->utilisateurConnecte->getId() == $interventionDemande->getCmsi()->getId())
           || ($this->utilisateurConnecte->hasRoleAmbassadeur()
           && $this->utilisateurConnecte->getId() == $interventionDemande->getAmbassadeur()->getId())
           || ($this->utilisateurConnecte->hasRoleDirecteur()
           && $interventionDemande->getDirecteur() != null
           && $this->utilisateurConnecte->getId() == $interventionDemande->getDirecteur()->getId())
           // Établissement toujours par défaut
           || ($this->utilisateurConnecte->getId() == $interventionDemande->getReferent()->getId())
           || $this->interventionRegroupementManager->interventionRegroupementsDemandePrincipaleHaveReferent(
               $interventionDemande,
               $this->utilisateurConnecte
           )
        ;
    }

    /**
     * Retourne si l'utilisateur connecté peut éditer une demande d'intervention ou non.
     *
     * @param InterventionDemande $interventionDemande
     *
     * @return bool
     */
    public function peutEditer(InterventionDemande $interventionDemande)
    {
        return $this->utilisateurConnecte->hasRoleCmsi()
           && $this->utilisateurConnecte->getId() == $interventionDemande->getCmsi()->getId()
        ;
    }

    /**
     * Retourne si l'utilisateur d'un établissement peut annuler cette demande d'intervention.
     *
     * @param InterventionDemande $interventionDemande
     * @param User                $utilisateur
     *
     * @return bool
     */
    public function etablissementPeutAnnulerDemande(InterventionDemande $interventionDemande, User $utilisateur)
    {
        if (!$utilisateur->hasRoleCmsi() && !$utilisateur->hasRoleAmbassadeur()) {
            if (count($interventionDemande->getInterventionRegroupementsDemandesRegroupees()) == 0
                && !$interventionDemande->interventionEtatEstAcceptationAmbassadeur()
                && !$interventionDemande->interventionEtatEstTermine()
                && !$interventionDemande->interventionEtatEstCloture()
                && !$interventionDemande->interventionEtatEstAnnuleEtablissement()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Envoie les relances pour les demandes d'intervention non traitées.
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
     */
    private function relanceInterventionDemandesEnAttenteCmsi()
    {
        $interventionDemandes = $this->repository->findByEtatAttenteCmsiPourRelance();

        /** @var InterventionDemande $interventionDemande */
        foreach ($interventionDemandes as $interventionDemande) {
            $interventionDemande->setCmsiDateDerniereRelance(new \DateTime());
            $this->save($interventionDemande);
            $this->interventionCourrielManager->envoiCourrielDemandeAcceptationCmsi(
                $interventionDemande->getCmsi(),
                $this->router->generate(
                    'hopital_numerique_intervention_demande_voir',
                    ['id' => $interventionDemande->getId()],
                    true
                )
            );
        }
    }

    /**
     * Envoie les relances pour les demandes d'intervention acceptées par le CMSI non traitées.
     */
    private function relanceInterventionDemandesAcceptationCmsi()
    {
        $interventionDemandes = $this->repository->findByEtatAcceptationCmsiPourRelance();

        /** @var InterventionDemande $interventionDemande */
        foreach ($interventionDemandes as $interventionDemande) {
            $interventionDemande->setInterventionEtat(
                $this->interventionEtatManager->getInterventionEtatAcceptationCmsiRelance1()
            );
            $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
            $this->save($interventionDemande);
            $this->interventionCourrielManager->envoiCourrielRelanceAmbassadeur1(
                $interventionDemande->getAmbassadeur(),
                $this->router->generate(
                    'hopital_numerique_intervention_demande_voir',
                    ['id' => $interventionDemande->getId()],
                    true
                )
            );
        }
    }

    /**
     * Envoie les relances pour les demandes d'intervention non traitées en relance ambassadeur 1.
     */
    private function relanceInterventionDemandesRelanceAmbassadeur1()
    {
        $interventionDemandes = $this->repository->findByEtatRelanceAmbassadeur1PourRelance();

        /** @var InterventionDemande $interventionDemande */
        foreach ($interventionDemandes as $interventionDemande) {
            $interventionDemande->setInterventionEtat(
                $this->interventionEtatManager->getInterventionEtatAcceptationCmsiRelance2()
            );
            $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
            $this->save($interventionDemande);
            $this->interventionCourrielManager->envoiCourrielRelanceAmbassadeur2(
                $interventionDemande->getAmbassadeur(),
                $this->router->generate(
                    'hopital_numerique_intervention_demande_voir',
                    ['id' => $interventionDemande->getId()],
                    true
                )
            );
        }
    }

    /**
     * Envoie les relances pour les demandes d'intervention non traitées en relance ambassadeur 2.
     */
    private function relanceInterventionDemandesRelanceAmbassadeur2()
    {
        $interventionDemandes = $this->repository->findByEtatRelanceAmbassadeur2PourRelance();

        /** @var InterventionDemande $interventionDemande */
        foreach ($interventionDemandes as $interventionDemande) {
            $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
            $interventionDemande->setInterventionEtat($this->interventionEtatManager->getInterventionEtatCloture());
            $this->save($interventionDemande);
            $this->interventionCourrielManager->envoiCourrielRelanceAmbassadeurCloture(
                $interventionDemande->getCmsi(),
                $interventionDemande->getAmbassadeur(),
                $interventionDemande->getReferent(),
                $this->router->generate(
                    'hopital_numerique_intervention_demande_voir',
                    ['id' => $interventionDemande->getId()],
                    true
                )
            );
        }
    }

    /**
     * Envoie des simples relances (aucun enregistrement en base de données).
     */
    public function relanceSimple()
    {
        $this->relanceSimpleInterventionDemandesEnAttenteCmsi();
    }

    /**
     * Envoie les relances pour les demandes d'intervention en attente CMSI non traitées.
     */
    private function relanceSimpleInterventionDemandesEnAttenteCmsi()
    {
        $interventionDemandes = $this->repository->findBy(
            ['interventionEtat' => $this->interventionEtatManager->getInterventionEtatAttenteCmsi()]
        );

        foreach ($interventionDemandes as $interventionDemande) {
            $this->interventionCourrielManager->envoiCourrielRelanceAttenteCmsi($interventionDemande);
        }
    }

    /**
     * Retourne les données formatées pour la création du grid des nouvelles demandes d'intervention pour le CMSI.
     *
     * @return array Les données pour le grid des nouvelles demandes d'intervention
     */
    public function getGridDonneesCmsiDemandesNouvelles()
    {
        $interventionDemandes = $this->repository->getGridDonneesCmsiDemandesNouvelles($this->utilisateurConnecte);

        $interventionDemandes = $this->reorderInterventionDemandeForGrid($interventionDemandes);

        return array_values($interventionDemandes);
    }

    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention traitées pour le CMSI.
     *
     * @return array Les données pour le grid des demandes d'intervention traitées
     */
    public function getGridDonneesCmsiDemandesTraitees()
    {
        $interventionDemandes = $this->repository->getGridDonneesCmsiDemandesTraitees($this->utilisateurConnecte);

        $interventionDemandes = $this->reorderInterventionDemandeForGrid($interventionDemandes);

        return array_values($interventionDemandes);
    }

    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour le directeur.
     *
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonneesDirecteurSuiviDemandes()
    {
        $interventionDemandes = $this->repository->getGridDonneesDirecteurSuiviDemandes($this->utilisateurConnecte);

        $interventionDemandes = $this->reorderInterventionDemandeForGrid($interventionDemandes);

        return array_values($interventionDemandes);
    }

    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour l'ambassadeur.
     *
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonneesAmbassadeurDemandes()
    {
        $interventionDemandes = $this->repository->getGridDonneesAmbassadeurDemandes($this->utilisateurConnecte);

        $interventionDemandes = $this->reorderInterventionDemandeForGrid($interventionDemandes);

        return array_values($interventionDemandes);
    }

    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour l'établissement.
     *
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonneesEtablissementDemandes()
    {
        $referent = $this->utilisateurConnecte;
        $interventionDemandes = $this->repository->getGridDonneesEtablissementDemandes($referent);

        $interventionDemandes = $this->reorderInterventionDemandeForGrid($interventionDemandes);

        return array_values($interventionDemandes);
    }

    /**
     * Retourne les données formatées pour la création du grid des demandes d'intervention pour l'administration.
     *
     * @return array Les données pour le grid des demandes d'intervention
     */
    public function getGridDonneesAdminDemandes()
    {
        $interventionDemandes = $this->repository->getGridDonneesAdminDemandes();

        $interventionDemandes = $this->reorderInterventionDemandeForGrid($interventionDemandes);

        return array_values($interventionDemandes);
    }

    /**
     * Retourne les demandes d'intervention similaire par rapport à l'ambassadeur d'une demande d'intervention.
     *
     * @param InterventionDemande $interventionDemande
     *
     * @return InterventionDemande[] Les demandes d'intervention similaires par rapport à l'ambassadeur
     */
    public function getInterventionsSimilairesParAmbassadeur(InterventionDemande $interventionDemande)
    {
        return $this->repository->getInterventionsSimilairesParAmbassadeur($interventionDemande);
    }

    /**
     * Retourne les demandes d'intervention similaire par rapport aux objets d'une demande d'intervention.
     *
     * @param InterventionDemande $interventionDemande
     *
     * @return InterventionDemande[] Les demandes d'intervention similaires par rapport aux objets
     */
    public function getInterventionsSimilairesParObjets(InterventionDemande $interventionDemande)
    {
        return $this->repository->getInterventionsSimilairesParObjets($interventionDemande);
    }

    /**
     * Change l'ambassadeur d'une demande d'intervention.
     *
     * @param InterventionDemande $interventionDemande La demande d'intervention qui change d'ambassadeur
     * @param User                $nouvelAmbassadeur   Le nouvelle ambassadeur de la demande
     *
     * @return bool VRAI ssi le nouvel ambassadeur est validé et enregistré
     */
    public function changeAmbassadeur(InterventionDemande $interventionDemande, User $nouvelAmbassadeur)
    {
        $ancienAmbassadeur = $interventionDemande->getAmbassadeur();

        if ($nouvelAmbassadeur->getId() != $ancienAmbassadeur->getId()) {
            $this->changeAmbassadeurDemandesRegroupees($interventionDemande, $nouvelAmbassadeur);

            if (!$interventionDemande->haveAncienAmbassadeur($ancienAmbassadeur)) {
                $interventionDemande->addAncienAmbassadeur($ancienAmbassadeur);
            }
            $interventionDemande->setAmbassadeur($nouvelAmbassadeur);
            $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
            $this->save($interventionDemande);

            $this->interventionCourrielManager->envoiCourrielChangementAmbassadeur(
                [
                    $interventionDemande->getCmsi(),
                    $interventionDemande->getReferent(),
                    $nouvelAmbassadeur,
                ],
                $nouvelAmbassadeur,
                $this->router->generate(
                    'hopital_numerique_intervention_demande_voir',
                    ['id' => $interventionDemande->getId()],
                    true
                )
            );

            return true;
        }

        return false;
    }

    /**
     * Change l'ambassadeur des demandes d'intervention regroupées d'une demande.
     *
     * @param InterventionDemande $interventionDemande
     * @param User                $nouvelAmbassadeur
     */
    private function changeAmbassadeurDemandesRegroupees(
        InterventionDemande $interventionDemande,
        User $nouvelAmbassadeur
    ) {
        $interventionRegroupements = $this->interventionRegroupementManager->findBy(
            ['interventionDemandePrincipale' => $interventionDemande]
        );

        foreach ($interventionRegroupements as $interventionRegroupement) {
            $this->changeAmbassadeur($interventionRegroupement->getInterventionDemandeRegroupee(), $nouvelAmbassadeur);
        }
    }

    /**
     * Vérifie et change l'état d'une demande d'intervention.
     *
     * @param InterventionDemande $interventionDemande
     * @param Reference           $interventionEtat
     * @param string|null         $messageJustificationChangementEtat
     *
     * @return bool VRAI ssi l'état a été modifié
     */
    public function changeEtat(
        InterventionDemande $interventionDemande,
        Reference $interventionEtat,
        $messageJustificationChangementEtat = null
    ) {
        $this->changeEtatInterventionDemandesRegroupees(
            $interventionDemande,
            $interventionEtat,
            $messageJustificationChangementEtat
        );

        if ($this->utilisateurConnecte->hasRoleCmsi()
            && $interventionDemande->getCmsi()->getId() == $this->utilisateurConnecte->getId()
        ) {
            return $this->changeEtatPourCmsi(
                $interventionDemande,
                $interventionEtat,
                $messageJustificationChangementEtat
            );
        } elseif ($this->utilisateurConnecte->hasRoleAmbassadeur()
                  && $interventionDemande->getAmbassadeur()->getId() == $this->utilisateurConnecte->getId()
        ) {
            return $this->changeEtatPourAmbassadeur(
                $interventionDemande,
                $interventionEtat,
                $messageJustificationChangementEtat
            );
        } else {
            return $this->changeEtatPourEtablissement($interventionDemande, $interventionEtat);
        }
    }

    /**
     * Change l'état des demandes d'intervention regroupées d'une demande.
     *
     * @param InterventionDemande $interventionDemande
     * @param Reference           $interventionEtat
     * @param string|null         $messageJustificationChangementEtat
     */
    private function changeEtatInterventionDemandesRegroupees(
        InterventionDemande $interventionDemande,
        Reference $interventionEtat,
        $messageJustificationChangementEtat
    ) {
        foreach ($interventionDemande->getInterventionRegroupementsDemandesRegroupees() as $interventionRegroupement) {
            $this->changeEtat(
                $interventionRegroupement->getInterventionDemandeRegroupee(),
                $interventionEtat,
                $messageJustificationChangementEtat
            );
        }
    }

    /**
     * Vérifie et change l'état d'une demande d'intervention pour un CMSI.
     *
     * @param InterventionDemande $interventionDemande
     * @param Reference           $interventionEtat
     * @param string|null         $messageJustificationChangementEtat
     *
     * @return bool VRAI ssi l'état a été modifié
     */
    private function changeEtatPourCmsi(
        InterventionDemande $interventionDemande,
        Reference $interventionEtat,
        $messageJustificationChangementEtat
    ) {
        if ($interventionDemande->interventionEtatEstDemandeInitiale()
            || $interventionDemande->interventionEtatEstAttenteCmsi()
        ) {
            if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatAttenteCmsiId()) {
                $interventionDemande->setInterventionEtat($interventionEtat);
                $interventionDemande->setCmsiDateDerniereRelance(new \DateTime());
                $this->save($interventionDemande);

                return true;
            } elseif (in_array(
                $interventionEtat->getId(),
                [
                    InterventionEtat::getInterventionEtatAcceptationCmsiId(),
                    InterventionEtat::getInterventionEtatRefusCmsiId(),
                ]
            )) {
                $interventionDemande->setInterventionEtat($interventionEtat);
                $interventionDemande->setCmsiDateChoix(new \DateTime());
                if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatRefusCmsiId()) {
                    if ($messageJustificationChangementEtat != null) {
                        $interventionDemande->setRefusMessage($messageJustificationChangementEtat);
                    }
                }

                $interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
                $this->save($interventionDemande);

                if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatRefusCmsiId()) {
                    $this->interventionCourrielManager->envoiCourrielEstRefuseCmsi($interventionDemande);
                } elseif ($interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationCmsiId()) {
                    $this->interventionCourrielManager->envoiCourrielDemandeAcceptationAmbassadeur(
                        $interventionDemande->getAmbassadeur(),
                        $this->router->generate(
                            'hopital_numerique_intervention_demande_voir',
                            ['id' => $interventionDemande->getId()],
                            true
                        )
                    );
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie et change l'état d'une demande d'intervention pour un ambassadeur.
     *
     * @param InterventionDemande $interventionDemande
     * @param Reference           $interventionEtat
     * @param string|null         $messageJustificationChangementEtat
     *
     * @return bool VRAI ssi l'état a été modifié
     */
    private function changeEtatPourAmbassadeur(
        InterventionDemande $interventionDemande,
        Reference $interventionEtat,
        $messageJustificationChangementEtat
    ) {
        if ($interventionDemande->interventionEtatEstAcceptationCmsi()) {
            if (in_array(
                $interventionEtat->getId(),
                [
                    InterventionEtat::getInterventionEtatAcceptationAmbassadeurId(),
                    InterventionEtat::getInterventionEtatRefusAmbassadeurId(),
                ]
            )) {
                $interventionDemande->setInterventionEtat($interventionEtat);
                $interventionDemande->setAmbassadeurDateChoix(new \DateTime());
                if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatRefusAmbassadeurId()) {
                    if ($messageJustificationChangementEtat != null) {
                        $interventionDemande->setRefusMessage($messageJustificationChangementEtat);
                    }
                } elseif ($interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationAmbassadeurId()) {
                    $interventionDemande->setEvaluationEtat(
                        $this->interventionEvaluationEtatManager->getInterventionEvaluationEtatAEvaluer()
                    );
                }

                $this->save($interventionDemande);

                if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatRefusAmbassadeurId()) {
                    $this->interventionCourrielManager->envoiCourrielEstRefuseAmbassadeur(
                        $interventionDemande->getReferent(),
                        $this->router->generate(
                            'hopital_numerique_intervention_demande_voir',
                            ['id' => $interventionDemande->getId()],
                            true
                        )
                    );
                } elseif ($interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationAmbassadeurId()) {
                    $this->interventionCourrielManager->envoiCourrielEstAccepteAmbassadeur(
                        $interventionDemande->getReferent(),
                        $this->router->generate(
                            'hopital_numerique_intervention_demande_voir',
                            ['id' => $interventionDemande->getId()],
                            true
                        )
                    );
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie et change l'état d'une demande d'intervention pour Annulé.
     *
     * @param InterventionDemande $interventionDemande
     * @param Reference           $interventionEtat
     *
     * @return bool VRAI ssi l'état a été modifié
     */
    private function changeEtatPourEtablissement(InterventionDemande $interventionDemande, Reference $interventionEtat)
    {
        if ($interventionEtat->getId() == InterventionEtat::getInterventionEtatTermineId()) {
            $interventionDemande->setInterventionEtat($interventionEtat);
            $this->save($interventionDemande);
        } elseif ($this->etablissementPeutAnnulerDemande($interventionDemande, $this->utilisateurConnecte)
                  && $interventionEtat->getId() == InterventionEtat::getInterventionEtatAnnulationEtablissementId()
        ) {
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
     * @param InterventionDemande $interventionDemande
     * @param Reference           $interventionEvaluationEtat
     */
    public function changeEvaluationEtat(
        InterventionDemande $interventionDemande,
        Reference $interventionEvaluationEtat
    ) {
        $this->changeEvaluationEtatInterventionDemandesRegroupees($interventionDemande, $interventionEvaluationEtat);

        $interventionDemande->setEvaluationEtat($interventionEvaluationEtat);

        if ($interventionEvaluationEtat->getId() === InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId()) {
            $interventionDemande->setEvaluationDate(new \DateTime());
        }

        $this->save($interventionDemande);
    }

    /**
     * Change l'état dévaluation des demandes d'intervention regroupées d'une demande.
     *
     * @param InterventionDemande $interventionDemande
     * @param Reference           $interventionEvaluationEtat
     */
    private function changeEvaluationEtatInterventionDemandesRegroupees(
        InterventionDemande $interventionDemande,
        Reference $interventionEvaluationEtat
    ) {
        foreach ($interventionDemande->getInterventionRegroupementsDemandesRegroupees() as $interventionRegroupement) {
            $this->changeEvaluationEtat(
                $interventionRegroupement->getInterventionDemandeRegroupee(),
                $interventionEvaluationEtat
            );
        }
    }

    /**
     * Retourne TRUE si le champ "Etat actuel" a été modifié.
     *
     * @param InterventionDemande $intervention l'intervention en question
     *
     * @return bool TRUE si le champ "Etat actuel" a été modifié
     */
    public function isEtatActuelUpdated(InterventionDemande $intervention)
    {
        return $this->repository->isEtatActuelUpdated($intervention);
    }

    /**
     * Retourne toutes les demandes pour l'export.
     *
     * @param int[] $allPrimaryKeys Les IDs des demandes à exporter
     *
     * @return InterventionDemande[] Demandes
     */
    public function findForExport($allPrimaryKeys)
    {
        return $this->getRepository()->findForExport($allPrimaryKeys);
    }

    /**
     * Retourne les demandes pour l'export.
     *
     * @param array $allPrimaryKeys
     * @param       $charset
     *
     * @return Response
     */
    public function getExportCsv(array $allPrimaryKeys, $charset)
    {
        $questionnaire = $this->questionnaireManager->findOneById(
            InterventionEvaluation::getEvaluationQuestionnaireId()
        );

        $exportTitres =
        [
            'Id',
            'Initiateur',
            'Demandeur',
            'Directeur',
            'Nom ES',
            'FINESS ES',
            'Région ES',
            'E-mail',
            'Téléphone',
            'Date demande',
            'Type d\'intervention',
            'État de l\'intervention',
            'CMSI',
            'Mail CMSI',
            'Tél CMSI',
            'Date de choix du CMSI',
            'Ambassadeur',
            'Région ambassadeur',
            'Mail ambassadeur',
            'Tél ambassadeur',
            'Date de choix de l\'ambassadeur',
            'Autres établissements',
            'Prods',
            'Autres objets',
            'Connaisances métier',
            'Connaissances SI',
            'Description du projet',
            'Description de la difficulté',
            'Champ libre',
            'Informations de RDV',
            'Commentaire CMSI',
            'État de l\'évaluation',
            'État du remboursement',
            'Facture',
            'Message de refus',
            'Date de dernière relance du CMSI',
            'Date de dernière relance de l\'ambassadeur',
        ];

        foreach ($questionnaire->getQuestions() as $question) {
            $exportTitres[] = $question->getLibelle();
        }

        $interventionDemandesExport = [];
        foreach ($this->findForExport($allPrimaryKeys) as $interventionDemande) {
            $prods = [];
            $connaissances = [];
            $connaissancesSI = [];
            foreach ($interventionDemande->getObjets() as $objet) {
                $prods[] = $objet->getTitre();
            }
            foreach ($interventionDemande->getConnaissances() as $connaissance) {
                $connaissances[] = $connaissance->getLibelle();
            }
            foreach ($interventionDemande->getConnaissancesSI() as $connaissanceSI) {
                $connaissancesSI[] = $connaissanceSI->getLibelle();
            }

            $interventionDemandeExport =
            [
                $interventionDemande->getId(),
                (null === $interventionDemande->getInterventionInitiateur() ? '' : $interventionDemande->getInterventionInitiateur()->__toString()),
                (null === $interventionDemande->getReferent() ? '' : $interventionDemande->getReferent()->getAppellation()),
                (null === $interventionDemande->getDirecteur() ? '' : $interventionDemande->getDirecteur()->getAppellation()),
                (null !== $interventionDemande->getReferent() && null !== $interventionDemande->getReferent()->getOrganization() ? $interventionDemande->getReferent()->getOrganization()->getNom() : ''),
                (null !== $interventionDemande->getReferent() && null !== $interventionDemande->getReferent()->getOrganization() ? $interventionDemande->getReferent()->getOrganization()->getFiness() : ''),
                (null !== $interventionDemande->getReferent() && null !== $interventionDemande->getReferent()->getOrganization() && null !== $interventionDemande->getReferent()->getOrganization()->getRegion() ? $interventionDemande->getReferent()->getOrganization()->getRegion()->getLibelle() : ''),
                $interventionDemande->getEmail(),
                $interventionDemande->getTelephone(),
                $interventionDemande->getDateCreation()->format('d/m/Y'),
                $interventionDemande->getInterventionType()->getLibelle(),
                (null === $interventionDemande->getInterventionEtat() ? '' : $interventionDemande->getInterventionEtat()->getLibelle()),
                (null === $interventionDemande->getCmsi() ? '' : $interventionDemande->getCmsi()->getAppellation()),
                (null === $interventionDemande->getCmsi() ? '' : $interventionDemande->getCmsi()->getEmail()),
                (null === $interventionDemande->getCmsi() ? '' : $interventionDemande->getCmsi()->getPhoneNumber()),
                (null === $interventionDemande->getCmsiDateChoix() ? '' : $interventionDemande->getCmsiDateChoix()->format('d/m/Y')),
                (null === $interventionDemande->getAmbassadeur() ? '' : $interventionDemande->getAmbassadeur()->getAppellation()),
                (null !== $interventionDemande->getAmbassadeur() && null !== $interventionDemande->getAmbassadeur()->getRegion() ? $interventionDemande->getAmbassadeur()->getRegion()->getLibelle() : ''),
                (null === $interventionDemande->getAmbassadeur() ? '' : $interventionDemande->getAmbassadeur()->getEmail()),
                (null === $interventionDemande->getAmbassadeur() ? '' : $interventionDemande->getAmbassadeur()->getPhoneNumber()),
                (null === $interventionDemande->getAmbassadeurDateChoix() ? '' : $interventionDemande->getAmbassadeurDateChoix()->format('d/m/Y')),
                $interventionDemande->getAutresEtablissements(),
                implode("\n", $prods),
                $interventionDemande->getObjetsAutres(),
                implode("\n | ", $connaissances),
                implode("\n | ", $connaissancesSI),
                $interventionDemande->getDescription(),
                $interventionDemande->getDifficulteDescription(),
                $interventionDemande->getChampLibre(),
                $interventionDemande->getRdvInformations(),
                $interventionDemande->getCmsiCommentaire(),
                (null === $interventionDemande->getEvaluationEtat() ? '' : $interventionDemande->getEvaluationEtat()->getLibelle()),
                (null === $interventionDemande->getRemboursementEtat() ? '' : $interventionDemande->getRemboursementEtat()->getLibelle()),
                (null === $interventionDemande->getFacture() ? '' : $interventionDemande->getFacture()->__toString()),
                $interventionDemande->getRefusMessage(),
                (null === $interventionDemande->getCmsiDateDerniereRelance() ? '' : $interventionDemande->getCmsiDateDerniereRelance()->format('d/m/Y')),
                (null === $interventionDemande->getAmbassadeurDateDerniereRelance() ? '' : $interventionDemande->getAmbassadeurDateDerniereRelance()->format('d/m/Y')),
            ];

            $reponses = $this->reponseManager->findBy(['question' => $questionnaire->getQuestions()->toArray(), 'paramId' => $interventionDemande->getId()]);
            foreach ($questionnaire->getQuestions() as $question) {
                $reponseExiste = false;
                foreach ($reponses as $reponse) {
                    if ($reponse->getQuestion()->getId() == $question->getId()) {
                        if ($reponse->getQuestion()->getId() == 26) { // Autres productions
                            $objetTitres = '';
                            $objetIds = explode(',', trim($reponse->getReponse()));

                            if (count($objetIds) > 0) {
                                $objets = $this->objetManager->findBy(['id' => $objetIds]);
                                $objetsTitresTab = [];
                                foreach ($objets as $objet) {
                                    $objetsTitresTab[] = $objet->getTitre();
                                }
                                $objetTitres = implode("\n", $objetsTitresTab);
                            }

                            $interventionDemandeExport[] = $objetTitres;
                        } elseif (null !== $reponse->getReference()) {
                            $interventionDemandeExport[] = $reponse->getReference()->getLibelle();
                        } else {
                            $interventionDemandeExport[] = $reponse->getReponse();
                        }
                        $reponseExiste = true;
                        break;
                    }
                }
                if (false === $reponseExiste) {
                    $interventionDemandeExport[] = '';
                }
            }

            $interventionDemandesExport[] = $interventionDemandeExport;
        }

        return $this->exportCsv(
            $exportTitres,
            $interventionDemandesExport,
            'export-demandes-intervention.csv',
            $charset
        );
    }

    /**
     * @param $interventionDemandes
     *
     * @return array
     */
    private function reorderInterventionDemandeForGrid($interventionDemandes)
    {
        $interventionDemandesOrdered = [];

        foreach ($interventionDemandes as $interventionDemande) {
            $interventionDemandesOrdered[$interventionDemande['id']] = $interventionDemande;
            $interventionDemandesOrdered[$interventionDemande['id']]['idIntervention'] = $interventionDemande['id'];
        }

        return $interventionDemandesOrdered;
    }
}
