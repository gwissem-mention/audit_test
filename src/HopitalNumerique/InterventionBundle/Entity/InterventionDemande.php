<?php

namespace HopitalNumerique\InterventionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\InterventionBundle\Entity\InterventionRegroupement;

/**
 * Entité d'une demande d'intervention.
 *
 * @ORM\Table(
 *     name="hn_intervention_demande",
 *     indexes={
 *         @ORM\Index(name="fk_hn_intervention_core_user", columns={"referent_id"}),
 *         @ORM\Index(name="fk_hn_intervention_demande_core_user1", columns={"ambassadeur_id"}),
 *         @ORM\Index(name="fk_hn_intervention_demande_core_user2", columns={"cmsi_id"}),
 *         @ORM\Index(name="fk_hn_intervention_demande_core_user3", columns={"directeur_id"}),
 *         @ORM\Index(name="fk_hn_intervention_demande_hn_reference1", columns={"ref_intervention_type_id"}),
 *         @ORM\Index(name="fk_hn_intervention_demande_hn_reference2", columns={"ref_intervention_etat_id"}),
 *         @ORM\Index(name="fk_hn_intervention_demande_hn_intervention_initiateur1", columns={"intervinit_id"}),
 *         @ORM\Index(name="fk_hn_intervention_demande_hn_reference3", columns={"ref_evaluation_etat_id"}),
 *         @ORM\Index(name="fk_hn_intervention_demande_hn_reference4", columns={"ref_remboursement_etat_id"}),
 *         @ORM\Index(name="fk_hn_intervention_demande_date_creation", columns={"interv_date_creation"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="HopitalNumerique\InterventionBundle\Repository\InterventionDemandeRepository")
 */
class InterventionDemande
{
    /**
     * @var integer
     *
     * @ORM\Column(name="interv_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="interv_date_creation", type="datetime", nullable=false)
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="interv_cmsi_date_choix", type="datetime", nullable=true)
     */
    private $cmsiDateChoix;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="interv_ambassadeur_date_choix", type="datetime", nullable=true)
     */
    private $ambassadeurDateChoix;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="interv_cmsi_date_derniere_relance", type="datetime", nullable=true)
     */
    private $cmsiDateDerniereRelance;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="interv_ambassadeur_date_derniere_relance", type="datetime", nullable=true)
     */
    private $ambassadeurDateDerniereRelance;
    
    /**
     * @var string
     *
     * @ORM\Column(name="interv_autres_etablissements", type="text", columnDefinition="TEXT", nullable=true)
     */
    private $autresEtablissements;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_description", type="text", columnDefinition="TEXT", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_difficulte_description", type="text", columnDefinition="TEXT", nullable=true)
     */
    private $difficulteDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_champ_libre", type="text", columnDefinition="TEXT", nullable=true)
     */
    private $champLibre;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_rdv_informations", type="text", columnDefinition="TEXT", nullable=true)
     */
    private $rdvInformations;

    /**
     * @var string
     *
     * @ORM\Column(name="interv_cmsi_commentaire", type="text", columnDefinition="TEXT", nullable=true)
     */
    private $cmsiCommentaire;
    
    /**
     * @var string
     *
     * @ORM\Column(name="interv_refus_message", type="text", columnDefinition="TEXT", nullable=true)
     */
    private $refusMessage;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="referent_id", referencedColumnName="usr_id")
     * })
     * @Nodevo\Javascript(class="validate[required]")
     */
    private $referent;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ambassadeur_id", referencedColumnName="usr_id")
     * })
     */
    private $ambassadeur;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cmsi_id", referencedColumnName="usr_id")
     * })
     */
    private $cmsi;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="directeur_id", referencedColumnName="usr_id")
     * })
     */
    private $directeur;

    /**
     * @var \InterventionInitiateur
     *
     * @ORM\ManyToOne(targetEntity="InterventionInitiateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="intervinit_id", referencedColumnName="intervinit_id")
     * })
     */
    private $interventionInitiateur;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_intervention_type_id", referencedColumnName="ref_id", nullable=false)
     * })
     * @Assert\NotNull(message="Un type d'intervention doit être choisi.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    private $interventionType;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_intervention_etat_id", referencedColumnName="ref_id")
     * })
     */
    private $interventionEtat;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_evaluation_etat_id", referencedColumnName="ref_id")
     * })
     */
    private $evaluationEtat;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_remboursement_etat_id", referencedColumnName="ref_id")
     * })
     */
    private $remboursementEtat;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinTable(name="hn_intervention_ambassadeur_historique",
     *   joinColumns={
     *     @ORM\JoinColumn(name="interv_id", referencedColumnName="interv_id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="ambassadeur_ancien_id", referencedColumnName="usr_id")
     *   }
     * )
     */
    private $ancienAmbassadeurs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\EtablissementBundle\Entity\Etablissement")
     * @ORM\JoinTable(name="hn_intervention_etablissement_rattache",
     *   joinColumns={
     *     @ORM\JoinColumn(name="interv_id", referencedColumnName="interv_id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="eta_id", referencedColumnName="eta_id")
     *   }
     * )
     */
    private $etablissements;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\ObjetBundle\Entity\Objet")
     * @ORM\JoinTable(name="hn_intervention_objet",
     *   joinColumns={
     *     @ORM\JoinColumn(name="interv_id", referencedColumnName="interv_id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id")
     *   }
     * )
     * @Assert\Count(min=1,minMessage="Au moins une production doit être choisie.")
     */
    private $objets;

    /**
     * @var \HopitalNumerique\InterventionBundle\Entity\InterventionRegroupement[]
     * 
     * @ORM\OneToMany(targetEntity="InterventionRegroupement", mappedBy="interventionDemandeRegroupee", cascade={"persist", "remove" })
     */
    private $interventionRegroupementsDemandesPrincipales;

    /**
     * @var \HopitalNumerique\InterventionBundle\Entity\InterventionRegroupement[]
     * 
     * @ORM\OneToMany(targetEntity="InterventionRegroupement", mappedBy="interventionDemandePrincipale", cascade={"persist", "remove" })
     */
    private $interventionRegroupementsDemandesRegroupees;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ambassadeurs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->etablissements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->objets = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return InterventionDemande
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set cmsiDateChoix
     *
     * @param \DateTime $cmsiDateChoix
     * @return InterventionDemande
     */
    public function setCmsiDateChoix($cmsiDateChoix)
    {
        $this->cmsiDateChoix = $cmsiDateChoix;

        return $this;
    }

    /**
     * Get cmsiDateChoix
     *
     * @return \DateTime 
     */
    public function getCmsiDateChoix()
    {
        return $this->cmsiDateChoix;
    }

    /**
     * Set ambassadeurDateChoix
     *
     * @param \DateTime $ambassadeurDateChoix
     * @return InterventionDemande
     */
    public function setAmbassadeurDateChoix($ambassadeurDateChoix)
    {
        $this->ambassadeurDateChoix = $ambassadeurDateChoix;

        return $this;
    }

    /**
     * Get ambassadeurDateChoix
     *
     * @return \DateTime 
     */
    public function getAmbassadeurDateChoix()
    {
        return $this->ambassadeurDateChoix;
    }

    
    /**
     * Set cmsiDateDerniereRelance
     *
     * @param \DateTime $cmsiDateDerniereRelance
     * @return InterventionDemande
     */
    public function setCmsiDateDerniereRelance($cmsiDateDerniereRelance)
    {
        $this->cmsiDateDerniereRelance = $cmsiDateDerniereRelance;
    
        return $this;
    }
    
    /**
     * Get cmsiDateDerniereRelance
     *
     * @return \DateTime
     */
    public function getCmsiDateDerniereRelance()
    {
        return $this->cmsiDateDerniereRelance;
    }
    
    /**
     * Set ambassadeurDateDerniereRelance
     *
     * @param \DateTime $ambassadeurDateDerniereRelance
     * @return InterventionDemande
     */
    public function setAmbassadeurDateDerniereRelance($ambassadeurDateDerniereRelance)
    {
        $this->ambassadeurDateDerniereRelance = $ambassadeurDateDerniereRelance;
    
        return $this;
    }
    
    /**
     * Get ambassadeurDateDerniereRelance
     *
     * @return \DateTime
     */
    public function getAmbassadeurDateDerniereRelance()
    {
        return $this->ambassadeurDateDerniereRelance;
    }
    
    /**
     * Set autresEtablissements
     *
     * @param string $autresEtablissements
     * @return InterventionDemande
     */
    public function setAutresEtablissements($autresEtablissements)
    {
        $this->autresEtablissements = $autresEtablissements;

        return $this;
    }

    /**
     * Get autresEtablissements
     *
     * @return string 
     */
    public function getAutresEtablissements()
    {
        return $this->autresEtablissements;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return InterventionDemande
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set difficulteDescription
     *
     * @param string $difficulteDescription
     * @return InterventionDemande
     */
    public function setDifficulteDescription($difficulteDescription)
    {
        $this->difficulteDescription = $difficulteDescription;

        return $this;
    }

    /**
     * Get difficulteDescription
     *
     * @return string 
     */
    public function getDifficulteDescription()
    {
        return $this->difficulteDescription;
    }

    /**
     * Set champLibre
     *
     * @param string $champLibre
     * @return InterventionDemande
     */
    public function setChampLibre($champLibre)
    {
        $this->champLibre = $champLibre;

        return $this;
    }

    /**
     * Get champLibre
     *
     * @return string 
     */
    public function getChampLibre()
    {
        return $this->champLibre;
    }

    /**
     * Set rdvInformations
     *
     * @param string $rdvInformations
     * @return InterventionDemande
     */
    public function setRdvInformations($rdvInformations)
    {
        $this->rdvInformations = $rdvInformations;

        return $this;
    }

    /**
     * Get rdvInformations
     *
     * @return string 
     */
    public function getRdvInformations()
    {
        return $this->rdvInformations;
    }

    /**
     * Set refusMessage
     *
     * @param string $refusMessage
     * @return InterventionDemande
     */
    public function setRefusMessage($refusMessage)
    {
        $this->refusMessage = $refusMessage;

        return $this;
    }

    /**
     * Get refusMessage
     *
     * @return string 
     */
    public function getRefusMessage()
    {
        return $this->refusMessage;
    }
    
    /**
     * Set cmsiCommentaire
     *
     * @param string $cmsiCommentaire
     * @return InterventionDemande
     */
    public function setCmsiCommentaire($cmsiCommentaire)
    {
        $this->cmsiCommentaire = $cmsiCommentaire;
    
        return $this;
    }
    
    /**
     * Get cmsiCommentaire
     *
     * @return string
     */
    public function getCmsiCommentaire()
    {
        return $this->cmsiCommentaire;
    }
    
    /**
     * Set referent
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $referent
     * @return InterventionDemande
     */
    public function setReferent(\HopitalNumerique\UserBundle\Entity\User $referent = null)
    {
        $this->referent = $referent;

        return $this;
    }

    /**
     * Get referent
     *
     * @return \HopitalNumerique\UserBundle\Entity\User 
     */
    public function getReferent()
    {
        return $this->referent;
    }

    /**
     * Set ambassadeur
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur
     * @return InterventionDemande
     */
    public function setAmbassadeur(\HopitalNumerique\UserBundle\Entity\User $ambassadeur = null)
    {
        $this->ambassadeur = $ambassadeur;

        return $this;
    }

    /**
     * Get ambassadeur
     *
     * @return \HopitalNumerique\UserBundle\Entity\User 
     */
    public function getAmbassadeur()
    {
        return $this->ambassadeur;
    }

    /**
     * Set cmsi
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi
     * @return InterventionDemande
     */
    public function setCmsi(\HopitalNumerique\UserBundle\Entity\User $cmsi = null)
    {
        $this->cmsi = $cmsi;

        return $this;
    }

    /**
     * Get cmsi
     *
     * @return \HopitalNumerique\UserBundle\Entity\User 
     */
    public function getCmsi()
    {
        return $this->cmsi;
    }

    /**
     * Set directeur
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $directeur
     * @return InterventionDemande
     */
    public function setDirecteur(\HopitalNumerique\UserBundle\Entity\User $directeur = null)
    {
        $this->directeur = $directeur;

        return $this;
    }

    /**
     * Get directeur
     *
     * @return \HopitalNumerique\UserBundle\Entity\User 
     */
    public function getDirecteur()
    {
        return $this->directeur;
    }

    /**
     * Set interventionInitiateur
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur $interventionInitiateur
     * @return InterventionDemande
     */
    public function setInterventionInitiateur(
            \HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur $interventionInitiateur = null)
    {
        $this->interventionInitiateur = $interventionInitiateur;

        return $this;
    }

    /**
     * Get interventionInitiateur
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur 
     */
    public function getInterventionInitiateur()
    {
        return $this->interventionInitiateur;
    }

    /**
     * Set interventionType
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionType
     * @return InterventionDemande
     */
    public function setInterventionType(\HopitalNumerique\ReferenceBundle\Entity\Reference $interventionType = null)
    {
        $this->interventionType = $interventionType;

        return $this;
    }

    /**
     * Get interventionType
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getInterventionType()
    {
        return $this->interventionType;
    }

    /**
     * Set interventionEtat
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEtat
     * @return InterventionDemande
     */
    public function setInterventionEtat(\HopitalNumerique\ReferenceBundle\Entity\Reference $interventionEtat = null)
    {
        $this->interventionEtat = $interventionEtat;

        return $this;
    }

    /**
     * Get interventionEtat
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getInterventionEtat()
    {
        return $this->interventionEtat;
    }

    /**
     * Set evaluationEtat
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $evaluationEtat
     * @return InterventionDemande
     */
    public function setEvaluationEtat(\HopitalNumerique\ReferenceBundle\Entity\Reference $evaluationEtat = null)
    {
        $this->evaluationEtat = $evaluationEtat;

        return $this;
    }

    /**
     * Get evaluationEtat
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getEvaluationEtat()
    {
        return $this->evaluationEtat;
    }

    /**
     * Set remboursementEtat
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $remboursementEtat
     * @return InterventionDemande
     */
    public function setRemboursementEtat(\HopitalNumerique\ReferenceBundle\Entity\Reference $remboursementEtat = null)
    {
        $this->remboursementEtat = $remboursementEtat;

        return $this;
    }

    /**
     * Get remboursementEtat
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getRemboursementEtat()
    {
        return $this->remboursementEtat;
    }

    /**
     * Add ambassadeurs
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeurs
     * @return InterventionDemande
     */
    public function addAncienAmbassadeur(\HopitalNumerique\UserBundle\Entity\User $ancienAmbassadeur)
    {
        $this->ancienAmbassadeurs[] = $ancienAmbassadeur;

        return $this;
    }

    /**
     * Remove ambassadeurs
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeurs
     */
    public function removeAncienAmbassadeur(\HopitalNumerique\UserBundle\Entity\User $ancienAmbassadeur)
    {
        $this->ancienAmbassadeurs->removeElement($ancienAmbassadeur);
    }

    /**
     * Get ambassadeurs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAncienAmbassadeurs()
    {
        return $this->ancienAmbassadeurs;
    }

    /**
     * Add etablissements
     *
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissements
     * @return InterventionDemande
     */
    public function addEtablissement(\HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissements)
    {
        $this->etablissements[] = $etablissements;

        return $this;
    }

    /**
     * Remove etablissements
     *
     * @param \HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissements
     */
    public function removeEtablissement(\HopitalNumerique\EtablissementBundle\Entity\Etablissement $etablissements)
    {
        $this->etablissements->removeElement($etablissements);
    }

    /**
     * Get etablissements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEtablissements()
    {
        return $this->etablissements;
    }

    /**
     * Add objets
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $objets
     * @return InterventionDemande
     */
    public function addObjet(\HopitalNumerique\ObjetBundle\Entity\Objet $objets)
    {
        $this->objets[] = $objets;

        return $this;
    }

    /**
     * Remove objets
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $objets
     */
    public function removeObjet(\HopitalNumerique\ObjetBundle\Entity\Objet $objets)
    {
        $this->objets->removeElement($objets);
    }

    /**
     * Get objets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getObjets()
    {
        return $this->objets;
    }

    /**
     * Get InterventionRegroupementsDemandesPrincipales
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionRegroupement
     */
    public function getInterventionRegroupementsDemandesPrincipales()
    {
        return $this->interventionRegroupementsDemandesPrincipales;
    }
    /**
     * Get InterventionRegroupementsDemandesRegroupees
     *
     * @return \HopitalNumerique\InterventionBundle\Entity\InterventionRegroupement
     */
    public function getInterventionRegroupementsDemandesRegroupees()
    {
        return $this->interventionRegroupementsDemandesRegroupees;
    }
    
    
    
    /**
     * Retourne si la demande d'intervention a déjà eu un ambassadeur.
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur à vérifier parmi les anciens
     * @return boolean VRAI ssi l'ambassadeur avait été relié à cette demande d'intervention
     */
    public function haveAncienAmbassadeur(\HopitalNumerique\UserBundle\Entity\User $ambassadeur)
    {
        foreach ($this->ancienAmbassadeurs as $ancienAmbassadeur)
        {
            if ($ancienAmbassadeur->getId() == $ambassadeur->getId())
                return true;
        }
        
        return false;
    }
    
    
    /**
     * Retourne la date butoir pour le refus, validation ou mise en attente de la demande d'intervention par le CMSI.
     * Si priode modifiée, modifier également dans DemandesNouvellesGrid.php
     * 
     * @return \DateTime|null La date butoir du CMSI
     */
    public function getDateButoirCmsi()
    {
        if ($this->interventionEtatEstDemandeInitiale())
        {
            $dateButoir = new \DateTime($dateCreation);
            $dateButoir->add(new \DateInterval('P'.InterventionEtat::$VALIDATION_CMSI_NOMBRE_JOURS.'D'));
            return $dateButoir;
        }
        return null;
    }

    /**
     * Retourne la liste des IDs de régions où sont présents les établissements rattachés à cette demande.
     * 
     * @return integer[] IDs des régions où sont présents les établissements rattachés
     */
    public function getEtablissementsRattachesRegionsIds()
    {
        $regionsIds = array();
        
        foreach ($this->getEtablissementsRattachesRegions() as $region)
            $regionsIds[] = $region->getId();

        return $regionsIds;
    }
    /**
     * Retourne la liste des régions où sont présents les établissements rattachés à cette demande.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference[] Régions où sont présents les établissements rattachés
     */
    private function getEtablissementsRattachesRegions()
    {
        $regions = array();
        
        foreach ($this->etablissements as $etablissement)
        {
            if ($etablissement->getRegion() != null)
            {
                $regionDejaPresente = false;
                foreach ($regions as $region)
                {
                    if ($region->getId() == $etablissement->getRegion()->getId())
                    {
                        $regionDejaPresente = true;
                        break;
                    }
                }
                if (!$regionDejaPresente)
                    $regions[] = $etablissement->getRegion();
            }
        }
        
        return $regions;
    }
    
    /**
     * Retourne si l'état de l'intervention est Demande initiale.
     * 
     * @return boolean VRAI ssi l'état de l'intervention est Demande initiale
     */
    public function interventionEtatEstDemandeInitiale()
    {
        return ($this->interventionEtat->getId() == InterventionEtat::getInterventionEtatDemandeInitialeId());
    }
    /**
     * Retourne si l'état de l'intervention est Mise en attente par le CMSI.
     *
     * @return boolean VRAI ssi l'état de l'intervention est Mise en attente par le CMSI
     */
    public function interventionEtatEstAttenteCmsi()
    {
        return ($this->interventionEtat->getId() == InterventionEtat::getInterventionEtatAttenteCmsiId());
    }
    /**
     * Retourne si l'état de l'intervention est Validé par le CMSI.
     *
     * @return boolean VRAI ssi l'état de l'intervention est Validé par le CMSI
     */
    public function interventionEtatEstAcceptationCmsi()
    {
        return ($this->interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationCmsiId());
    }
    /**
     * Retourne si l'état de l'intervention est Validé par l'ambassadeur.
     *
     * @return boolean VRAI ssi l'état de l'intervention est Validé par l'ambassadeur
     */
    public function interventionEtatEstAcceptationAmbassadeur()
    {
        return ($this->interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationAmbassadeurId());
    }
    /**
     * Retourne si l'état de l'intervention est Terminé.
     *
     * @return boolean VRAI ssi l'état de l'intervention est Terminé
     */
    public function interventionEtatEstTermine()
    {
        return ($this->interventionEtat->getId() == InterventionEtat::getInterventionEtatTermineId());
    }
    /**
     * Retourne si l'état de l'intervention est Clôturé.
     *
     * @return boolean VRAI ssi l'état de l'intervention est Clôturé
     */
    public function interventionEtatEstCloture()
    {
        return ($this->interventionEtat->getId() == InterventionEtat::getInterventionEtatClotureId());
    }
    /**
     * Retourne si l'état de l'intervention est Annulé par l'établissement.
     *
     * @return boolean VRAI ssi l'état de l'intervention est Annulé par l'établissement
     */
    public function interventionEtatEstAnnuleEtablissement()
    {
        return ($this->interventionEtat->getId() == InterventionEtat::getInterventionEtatAnnulationEtablissementId());
    }

    /**
     * Retourne si l'état de l'évaluation est À évaluer.
     *
     * @return boolean VRAI ssi l'état de l'évaluation est À évaluer
     */
    public function evaluationEtatEstAEvaluer()
    {
        return ($this->evaluationEtat != null && $this->evaluationEtat->getId() == InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId());
    }
    /**
     * Retourne si l'état de l'évaluation est Évalué.
     *
     * @return boolean VRAI ssi l'état de l'évaluation est Évalué
     */
    public function evaluationEtatEstEvalue()
    {
        return ($this->evaluationEtat != null && $this->evaluationEtat->getId() == InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId());
    }
}
