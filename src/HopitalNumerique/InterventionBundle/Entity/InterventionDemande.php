<?php

namespace HopitalNumerique\InterventionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\PaiementBundle\Entity\Facture;
use HopitalNumerique\PaiementBundle\Entity\FactureAnnulee;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

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
     * @var int
     *
     * @ORM\Column(name="interv_id", type="integer", nullable=false, options={"unsigned":true})
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
     * @var \DateTime
     *
     * @ORM\Column(name="interv_date_evaluation", type="datetime", nullable=true)
     */
    private $evaluationDate;

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
     * @ORM\Column(name="interv_objets_autres", type="text", columnDefinition="TEXT", nullable=true)
     */
    private $objetsAutres;

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
     * @var int
     *
     * @ORM\Column(name="interv_total", type="integer", nullable=true)
     */
    private $total;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User", inversedBy="interventionDemandesReferent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="referent_id", referencedColumnName="usr_id")
     * })
     * @Nodevo\Javascript(class="validate[required]")
     */
    private $referent;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User", inversedBy="interventionDemandesAmbassadeur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ambassadeur_id", referencedColumnName="usr_id")
     * })
     * @Assert\NotNull(message="Un ambassadeur doit être choisi.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    private $ambassadeur;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User", inversedBy="interventionDemandesCmsi")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cmsi_id", referencedColumnName="usr_id")
     * })
     */
    private $cmsi;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User", inversedBy="interventionDemandesDirecteur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="directeur_id", referencedColumnName="usr_id")
     * })
     */
    private $directeur;

    /**
     * @var InterventionInitiateur
     *
     * @ORM\ManyToOne(targetEntity="InterventionInitiateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="intervinit_id", referencedColumnName="intervinit_id")
     * })
     */
    private $interventionInitiateur;

    /**
     * @var Reference
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
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_intervention_etat_id", referencedColumnName="ref_id")
     * })
     */
    private $interventionEtat;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_evaluation_etat_id", referencedColumnName="ref_id")
     * })
     */
    private $evaluationEtat;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ref_remboursement_etat_id", referencedColumnName="ref_id")
     * })
     */
    private $remboursementEtat;

    /**
     * @var Collection
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
     * @var Collection
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
     * @var Collection
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
     */
    private $objets;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_intervention_connaissance_metier",
     *   joinColumns={
     *     @ORM\JoinColumn(name="interv_id", referencedColumnName="interv_id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")
     *   }
     * )
     */
    private $connaissances;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_intervention_connaissance_si",
     *   joinColumns={
     *     @ORM\JoinColumn(name="interv_id", referencedColumnName="interv_id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")
     *   }
     * )
     */
    private $connaissancesSI;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionRegroupement", mappedBy="interventionDemandeRegroupee", cascade={"persist", "remove" })
     */
    private $interventionRegroupementsDemandesPrincipales;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionRegroupement", mappedBy="interventionDemandePrincipale", cascade={"persist", "remove"})
     */
    private $interventionRegroupementsDemandesRegroupees;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\PaiementBundle\Entity\Facture", cascade={"persist"}, inversedBy="interventions")
     * @ORM\JoinColumn(name="fac_id", referencedColumnName="fac_id", nullable=true)
     */
    protected $facture;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\PaiementBundle\Entity\FactureAnnulee", mappedBy="interventions")
     */
    private $facturesAnnulees;

    //Ajout GME:
    /**
     * @var string
     *
     * @Assert\NotBlank(message="L'adresse éléctronique ne peut pas être vide.")
     * @Assert\Regex(pattern= "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]{2,}[.][a-zA-Z]{2,3}$/")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom de compte.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom de compte."
     * )
     * @Nodevo\Javascript(class="validate[required,custom[email]]")
     * @ORM\Column(name="interv_email", type="string", length=50, nullable=true, options = {"comment" = "Adresse électronique"})
     */
    protected $email;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = "14",
     *      max = "14",
     *      minMessage="Le numéro de téléphone direct doit être composé de {{ limit }} caractères.",
     *      maxMessage="Le numéro de téléphone direct doit être composé de {{ limit }} caractères."
     * )
     * @Nodevo\Javascript(class="validate[minSize[14],maxSize[14]],custom[phone]", mask="99 99 99 99 99")
     * @ORM\Column(name="interv_direct", type="string", length=14, nullable=true, options = {"comment" = "Téléphone de l utilisateur"})
     */
    protected $telephone;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->ambassadeurs = new ArrayCollection();
        $this->etablissements = new ArrayCollection();
        $this->objets = new ArrayCollection();
        $this->facture = null;
        $this->total = null;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return InterventionDemande
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation.
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set cmsiDateChoix.
     *
     * @param \DateTime $cmsiDateChoix
     *
     * @return InterventionDemande
     */
    public function setCmsiDateChoix($cmsiDateChoix)
    {
        $this->cmsiDateChoix = $cmsiDateChoix;

        return $this;
    }

    /**
     * Get cmsiDateChoix.
     *
     * @return \DateTime
     */
    public function getCmsiDateChoix()
    {
        return $this->cmsiDateChoix;
    }

    /**
     * Set ambassadeurDateChoix.
     *
     * @param \DateTime $ambassadeurDateChoix
     *
     * @return InterventionDemande
     */
    public function setAmbassadeurDateChoix($ambassadeurDateChoix)
    {
        $this->ambassadeurDateChoix = $ambassadeurDateChoix;

        return $this;
    }

    /**
     * Get ambassadeurDateChoix.
     *
     * @return \DateTime
     */
    public function getAmbassadeurDateChoix()
    {
        return $this->ambassadeurDateChoix;
    }

    /**
     * Set cmsiDateDerniereRelance.
     *
     * @param \DateTime $cmsiDateDerniereRelance
     *
     * @return InterventionDemande
     */
    public function setCmsiDateDerniereRelance($cmsiDateDerniereRelance)
    {
        $this->cmsiDateDerniereRelance = $cmsiDateDerniereRelance;

        return $this;
    }

    /**
     * Get cmsiDateDerniereRelance.
     *
     * @return \DateTime
     */
    public function getCmsiDateDerniereRelance()
    {
        return $this->cmsiDateDerniereRelance;
    }

    /**
     * Set ambassadeurDateDerniereRelance.
     *
     * @param \DateTime $ambassadeurDateDerniereRelance
     *
     * @return InterventionDemande
     */
    public function setAmbassadeurDateDerniereRelance($ambassadeurDateDerniereRelance)
    {
        $this->ambassadeurDateDerniereRelance = $ambassadeurDateDerniereRelance;

        return $this;
    }

    /**
     * Get ambassadeurDateDerniereRelance.
     *
     * @return \DateTime
     */
    public function getAmbassadeurDateDerniereRelance()
    {
        return $this->ambassadeurDateDerniereRelance;
    }

    /**
     * Set autresEtablissements.
     *
     * @param string $autresEtablissements
     *
     * @return InterventionDemande
     */
    public function setAutresEtablissements($autresEtablissements)
    {
        $this->autresEtablissements = $autresEtablissements;

        return $this;
    }

    /**
     * Get autresEtablissements.
     *
     * @return string
     */
    public function getAutresEtablissements()
    {
        return $this->autresEtablissements;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return InterventionDemande
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set difficulteDescription.
     *
     * @param string $difficulteDescription
     *
     * @return InterventionDemande
     */
    public function setDifficulteDescription($difficulteDescription)
    {
        $this->difficulteDescription = $difficulteDescription;

        return $this;
    }

    /**
     * Get difficulteDescription.
     *
     * @return string
     */
    public function getDifficulteDescription()
    {
        return $this->difficulteDescription;
    }

    /**
     * Set champLibre.
     *
     * @param string $champLibre
     *
     * @return InterventionDemande
     */
    public function setChampLibre($champLibre)
    {
        $this->champLibre = $champLibre;

        return $this;
    }

    /**
     * Get champLibre.
     *
     * @return string
     */
    public function getChampLibre()
    {
        return $this->champLibre;
    }

    /**
     * Set rdvInformations.
     *
     * @param string $rdvInformations
     *
     * @return InterventionDemande
     */
    public function setRdvInformations($rdvInformations)
    {
        $this->rdvInformations = $rdvInformations;

        return $this;
    }

    /**
     * Get rdvInformations.
     *
     * @return string
     */
    public function getRdvInformations()
    {
        return $this->rdvInformations;
    }

    /**
     * Set refusMessage.
     *
     * @param string $refusMessage
     *
     * @return InterventionDemande
     */
    public function setRefusMessage($refusMessage)
    {
        $this->refusMessage = $refusMessage;

        return $this;
    }

    /**
     * Get refusMessage.
     *
     * @return string
     */
    public function getRefusMessage()
    {
        return $this->refusMessage;
    }

    /**
     * Set cmsiCommentaire.
     *
     * @param string $cmsiCommentaire
     *
     * @return InterventionDemande
     */
    public function setCmsiCommentaire($cmsiCommentaire)
    {
        $this->cmsiCommentaire = $cmsiCommentaire;

        return $this;
    }

    /**
     * Get cmsiCommentaire.
     *
     * @return string
     */
    public function getCmsiCommentaire()
    {
        return $this->cmsiCommentaire;
    }

    /**
     * Set referent.
     *
     * @param User $referent
     *
     * @return InterventionDemande
     */
    public function setReferent(User $referent = null)
    {
        $this->referent = $referent;

        return $this;
    }

    /**
     * Get referent.
     *
     * @return User
     */
    public function getReferent()
    {
        return $this->referent;
    }

    /**
     * Set ambassadeur.
     *
     * @param User $ambassadeur
     *
     * @return InterventionDemande
     */
    public function setAmbassadeur(User $ambassadeur = null)
    {
        $this->ambassadeur = $ambassadeur;

        return $this;
    }

    /**
     * Get ambassadeur.
     *
     * @return User
     */
    public function getAmbassadeur()
    {
        return $this->ambassadeur;
    }

    /**
     * Set cmsi.
     *
     * @param User $cmsi
     *
     * @return InterventionDemande
     */
    public function setCmsi(User $cmsi = null)
    {
        $this->cmsi = $cmsi;

        return $this;
    }

    /**
     * Get cmsi.
     *
     * @return User
     */
    public function getCmsi()
    {
        return $this->cmsi;
    }

    /**
     * Set directeur.
     *
     * @param User $directeur
     *
     * @return InterventionDemande
     */
    public function setDirecteur(User $directeur = null)
    {
        $this->directeur = $directeur;

        return $this;
    }

    /**
     * Get directeur.
     *
     * @return User
     */
    public function getDirecteur()
    {
        return $this->directeur;
    }

    /**
     * Set interventionInitiateur.
     *
     * @param InterventionInitiateur $interventionInitiateur
     *
     * @return InterventionDemande
     */
    public function setInterventionInitiateur(InterventionInitiateur $interventionInitiateur = null)
    {
        $this->interventionInitiateur = $interventionInitiateur;

        return $this;
    }

    /**
     * Get interventionInitiateur.
     *
     * @return InterventionInitiateur
     */
    public function getInterventionInitiateur()
    {
        return $this->interventionInitiateur;
    }

    /**
     * Set interventionType.
     *
     * @param Reference $interventionType
     *
     * @return InterventionDemande
     */
    public function setInterventionType(Reference $interventionType = null)
    {
        $this->interventionType = $interventionType;

        return $this;
    }

    /**
     * Get interventionType.
     *
     * @return Reference
     */
    public function getInterventionType()
    {
        return $this->interventionType;
    }

    /**
     * Set interventionEtat.
     *
     * @param Reference $interventionEtat
     *
     * @return InterventionDemande
     */
    public function setInterventionEtat(Reference $interventionEtat = null)
    {
        $this->interventionEtat = $interventionEtat;

        return $this;
    }

    /**
     * Get interventionEtat.
     *
     * @return Reference
     */
    public function getInterventionEtat()
    {
        return $this->interventionEtat;
    }

    /**
     * Set evaluationEtat.
     *
     * @param Reference $evaluationEtat
     *
     * @return InterventionDemande
     */
    public function setEvaluationEtat(Reference $evaluationEtat = null)
    {
        $this->evaluationEtat = $evaluationEtat;

        return $this;
    }

    /**
     * Get evaluationEtat.
     *
     * @return Reference
     */
    public function getEvaluationEtat()
    {
        return $this->evaluationEtat;
    }

    /**
     * Set remboursementEtat.
     *
     * @param Reference $remboursementEtat
     *
     * @return InterventionDemande
     */
    public function setRemboursementEtat(Reference $remboursementEtat = null)
    {
        $this->remboursementEtat = $remboursementEtat;

        return $this;
    }

    /**
     * Get remboursementEtat.
     *
     * @return Reference
     */
    public function getRemboursementEtat()
    {
        return $this->remboursementEtat;
    }

    /**
     * Add ambassadeurs.
     *
     * @param User $ancienAmbassadeur
     *
     * @return InterventionDemande
     */
    public function addAncienAmbassadeur(User $ancienAmbassadeur)
    {
        $this->ancienAmbassadeurs[] = $ancienAmbassadeur;

        return $this;
    }

    /**
     * Remove ambassadeurs.
     *
     * @param User $ancienAmbassadeur
     */
    public function removeAncienAmbassadeur(User $ancienAmbassadeur)
    {
        $this->ancienAmbassadeurs->removeElement($ancienAmbassadeur);
    }

    /**
     * Get ambassadeurs.
     *
     * @return Collection
     */
    public function getAncienAmbassadeurs()
    {
        return $this->ancienAmbassadeurs;
    }

    /**
     * Add etablissements.
     *
     * @param Etablissement $etablissements
     *
     * @return InterventionDemande
     */
    public function addEtablissement(Etablissement $etablissements)
    {
        $this->etablissements[] = $etablissements;

        return $this;
    }

    /**
     * Remove etablissements.
     *
     * @param Etablissement $etablissements
     */
    public function removeEtablissement(Etablissement $etablissements)
    {
        $this->etablissements->removeElement($etablissements);
    }

    /**
     * Get etablissements.
     *
     * @return Collection
     */
    public function getEtablissements()
    {
        return $this->etablissements;
    }

    /**
     * Add objets.
     *
     * @param Objet $objets
     *
     * @return InterventionDemande
     */
    public function addObjet(Objet $objets)
    {
        $this->objets[] = $objets;

        return $this;
    }

    /**
     * Remove objets.
     *
     * @param Objet $objets
     */
    public function removeObjet(Objet $objets)
    {
        $this->objets->removeElement($objets);
    }

    /**
     * Get objets.
     *
     * @return Collection
     */
    public function getObjets()
    {
        return $this->objets;
    }

    /**
     * Get InterventionRegroupementsDemandesPrincipales.
     *
     * @return Collection
     */
    public function getInterventionRegroupementsDemandesPrincipales()
    {
        return $this->interventionRegroupementsDemandesPrincipales;
    }

    /**
     * Get InterventionRegroupementsDemandesRegroupees.
     *
     * @return Collection
     */
    public function getInterventionRegroupementsDemandesRegroupees()
    {
        return $this->interventionRegroupementsDemandesRegroupees;
    }

    /**
     * Retourne si la demande d'intervention a déjà eu un ambassadeur.
     *
     * @param User $ambassadeur L'ambassadeur à vérifier parmi les anciens
     *
     * @return bool VRAI ssi l'ambassadeur avait été relié à cette demande d'intervention
     */
    public function haveAncienAmbassadeur(User $ambassadeur)
    {
        foreach ($this->ancienAmbassadeurs as $ancienAmbassadeur) {
            if ($ancienAmbassadeur->getId() == $ambassadeur->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne si la demande d'intervention a été regroupée (possède donc une demande principale).
     *
     * @return bool VRAI ssi la demande d'intervention a été regroupée
     */
    public function estDemandeRegroupee()
    {
        return count($this->interventionRegroupementsDemandesPrincipales) > 0;
    }

    /**
     * Retourne la liste des IDs de régions où sont présents les établissements rattachés à cette demande.
     *
     * @return int[] IDs des régions où sont présents les établissements rattachés
     */
    public function getEtablissementsRattachesRegionsIds()
    {
        $regionsIds = [];

        foreach ($this->getEtablissementsRattachesRegions() as $region) {
            $regionsIds[] = $region->getId();
        }

        return $regionsIds;
    }

    /**
     * Retourne la liste des régions où sont présents les établissements rattachés à cette demande.
     *
     * @return Reference[] Régions où sont présents les établissements rattachés
     */
    private function getEtablissementsRattachesRegions()
    {
        $regions = [];

        foreach ($this->etablissements as $etablissement) {
            if ($etablissement->getRegion() != null) {
                $regionDejaPresente = false;
                foreach ($regions as $region) {
                    if ($region->getId() == $etablissement->getRegion()->getId()) {
                        $regionDejaPresente = true;
                        break;
                    }
                }
                if (!$regionDejaPresente) {
                    $regions[] = $etablissement->getRegion();
                }
            }
        }

        return $regions;
    }

    /**
     * Retourne si l'état de l'intervention est Demande initiale.
     *
     * @return bool VRAI ssi l'état de l'intervention est Demande initiale
     */
    public function interventionEtatEstDemandeInitiale()
    {
        return $this->interventionEtat->getId() == InterventionEtat::getInterventionEtatDemandeInitialeId();
    }

    /**
     * Retourne si l'état de l'intervention est Mise en attente par le CMSI.
     *
     * @return bool VRAI ssi l'état de l'intervention est Mise en attente par le CMSI
     */
    public function interventionEtatEstAttenteCmsi()
    {
        return $this->interventionEtat->getId() == InterventionEtat::getInterventionEtatAttenteCmsiId();
    }

    /**
     * Retourne si l'état de l'intervention est Validé par le CMSI.
     *
     * @return bool VRAI ssi l'état de l'intervention est Validé par le CMSI
     */
    public function interventionEtatEstAcceptationCmsi()
    {
        $etat = $this->getInterventionEtat()->getId();
        if ($etat == InterventionEtat::getInterventionEtatAcceptationCmsiRelance2Id()) {
            return $this->interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationCmsiRelance2Id();
        } elseif ($etat == InterventionEtat::getInterventionEtatAcceptationCmsiRelance1Id()) {
            return $this->interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationCmsiRelance1Id();
        } else {
            return $this->interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationCmsiId();
        }
    }

    /**
     * Retourne si l'état de l'intervention est Validé par l'ambassadeur.
     *
     * @return bool VRAI ssi l'état de l'intervention est Validé par l'ambassadeur
     */
    public function interventionEtatEstAcceptationAmbassadeur()
    {
        return $this->interventionEtat->getId() == InterventionEtat::getInterventionEtatAcceptationAmbassadeurId();
    }

    /**
     * Retourne si l'état de l'intervention est Terminé.
     *
     * @return bool VRAI ssi l'état de l'intervention est Terminé
     */
    public function interventionEtatEstTermine()
    {
        return $this->interventionEtat->getId() == InterventionEtat::getInterventionEtatTermineId();
    }

    /**
     * Retourne si l'état de l'intervention est Clôturé.
     *
     * @return bool VRAI ssi l'état de l'intervention est Clôturé
     */
    public function interventionEtatEstCloture()
    {
        return $this->interventionEtat->getId() == InterventionEtat::getInterventionEtatClotureId();
    }

    /**
     * Retourne si l'état de l'intervention est Annulé par l'établissement.
     *
     * @return bool VRAI ssi l'état de l'intervention est Annulé par l'établissement
     */
    public function interventionEtatEstAnnuleEtablissement()
    {
        return $this->interventionEtat->getId() == InterventionEtat::getInterventionEtatAnnulationEtablissementId();
    }

    /**
     * Retourne si l'état de l'évaluation est À évaluer.
     *
     * @return bool VRAI ssi l'état de l'évaluation est À évaluer
     */
    public function evaluationEtatEstAEvaluer()
    {
        return $this->evaluationEtat != null
           && $this->evaluationEtat->getId() == InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId()
        ;
    }

    /**
     * Retourne si l'état de l'évaluation est Évalué.
     *
     * @return bool VRAI ssi l'état de l'évaluation est Évalué
     */
    public function evaluationEtatEstEvalue()
    {
        return $this->evaluationEtat != null
            && $this->evaluationEtat->getId() == InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId()
        ;
    }

    /**
     * Get facture.
     *
     * @return Facture $facture
     */
    public function getFacture()
    {
        return $this->facture;
    }

    /**
     * Set facture.
     *
     * @param Facture $facture
     *
     * @return InterventionDemande
     */
    public function setFacture(Facture $facture = null)
    {
        $this->facture = $facture;

        return $this;
    }

    /**
     * Get total.
     *
     * @return int $total
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set total.
     *
     * @param int $total
     *
     * @return InterventionDemande
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get objetsAutres.
     *
     * @return string $objetsAutres
     */
    public function getObjetsAutres()
    {
        return $this->objetsAutres;
    }

    /**
     * Set objetsAutres.
     *
     * @param string $objetsAutres
     *
     * @return InterventionDemande
     */
    public function setObjetsAutres($objetsAutres)
    {
        $this->objetsAutres = $objetsAutres;

        return $this;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return InterventionDemande
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set telephone.
     *
     * @param string $telephone
     *
     * @return InterventionDemande
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get telephone.
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Add interventionRegroupementsDemandesPrincipales.
     *
     * @param InterventionRegroupement $interventionRegroupementsDemandesPrincipales
     *
     * @return InterventionDemande
     */
    public function addInterventionRegroupementsDemandesPrincipale(
        InterventionRegroupement $interventionRegroupementsDemandesPrincipales
    ) {
        $this->interventionRegroupementsDemandesPrincipales[] = $interventionRegroupementsDemandesPrincipales;

        return $this;
    }

    /**
     * Remove interventionRegroupementsDemandesPrincipales.
     *
     * @param InterventionRegroupement $interventionRegroupementsDemandesPrincipales
     */
    public function removeInterventionRegroupementsDemandesPrincipale(
        InterventionRegroupement $interventionRegroupementsDemandesPrincipales
    ) {
        $this->interventionRegroupementsDemandesPrincipales->removeElement(
            $interventionRegroupementsDemandesPrincipales
        );
    }

    /**
     * Add interventionRegroupementsDemandesRegroupees.
     *
     * @param InterventionRegroupement $interventionRegroupementsDemandesRegroupees
     *
     * @return InterventionDemande
     */
    public function addInterventionRegroupementsDemandesRegroupee(
        InterventionRegroupement $interventionRegroupementsDemandesRegroupees
    ) {
        $this->interventionRegroupementsDemandesRegroupees[] = $interventionRegroupementsDemandesRegroupees;

        return $this;
    }

    /**
     * Remove interventionRegroupementsDemandesRegroupees.
     *
     * @param InterventionRegroupement $interventionRegroupementsDemandesRegroupees
     */
    public function removeInterventionRegroupementsDemandesRegroupee(
        InterventionRegroupement $interventionRegroupementsDemandesRegroupees
    ) {
        $this->interventionRegroupementsDemandesRegroupees->removeElement($interventionRegroupementsDemandesRegroupees);
    }

    /**
     * Add connaissances.
     *
     * @param Reference $connaissances
     *
     * @return InterventionDemande
     */
    public function addConnaissance(Reference $connaissances)
    {
        $this->connaissances[] = $connaissances;

        return $this;
    }

    /**
     * Remove connaissances.
     *
     * @param Reference $connaissances
     */
    public function removeConnaissance(Reference $connaissances)
    {
        $this->connaissances->removeElement($connaissances);
    }

    /**
     * Get connaissances.
     *
     * @return Collection
     */
    public function getConnaissances()
    {
        return $this->connaissances;
    }

    /**
     * Get connaissances.
     *
     * @return $this
     */
    public function setConnaissances($connaissances)
    {
        $this->connaissances = $connaissances;

        return $this;
    }

    /**
     * Add connaissancesSI.
     *
     * @param Reference $connaissancesSI
     *
     * @return InterventionDemande
     */
    public function addConnaissancesSI(Reference $connaissancesSI)
    {
        $this->connaissancesSI[] = $connaissancesSI;

        return $this;
    }

    /**
     * Remove connaissancesSI.
     *
     * @param Reference $connaissancesSI
     */
    public function removeConnaissancesSI(Reference $connaissancesSI)
    {
        $this->connaissancesSI->removeElement($connaissancesSI);
    }

    /**
     * Get connaissancesSI.
     *
     * @return Collection
     */
    public function getConnaissancesSI()
    {
        return $this->connaissancesSI;
    }

    /**
     * Get connaissancesSI.
     *
     * @return $this
     */
    public function setConnaissancesSI($connaissancesSI)
    {
        $this->connaissancesSI = $connaissancesSI;

        return $this;
    }

    /**
     * @return array
     */
    public function getConnaissancesByParent()
    {
        $connaissances = $this->connaissances;
        $connaissancesOrdered = [];

        foreach ($connaissances as $connaissance) {
            $connaissancesOrdered[0][] = $connaissance;
        }

        return $connaissancesOrdered;
    }

    /**
     * @return array
     */
    public function getConnaissancesSIByParent()
    {
        $connaissancesSI = $this->connaissancesSI;
        $connaissancesOrdered = [];

        foreach ($connaissancesSI as $connaissance) {
            if (is_null($connaissance->getParent())) {
                continue;
            }

            if (!array_key_exists($connaissance->getParent()->getId(), $connaissancesOrdered)) {
                $connaissancesOrdered[$connaissance->getParent()->getId()] = [];
            }

            $connaissancesOrdered[$connaissance->getParent()->getId()][] = $connaissance;
        }

        return $connaissancesOrdered;
    }

    /**
     * Set evaluationDate.
     *
     * @param \DateTime $evaluationDate
     *
     * @return InterventionDemande
     */
    public function setEvaluationDate($evaluationDate)
    {
        $this->evaluationDate = $evaluationDate;

        return $this;
    }

    /**
     * Get evaluationDate.
     *
     * @return \DateTime
     */
    public function getEvaluationDate()
    {
        return $this->evaluationDate;
    }

    /**
     * Add facturesAnnulee.
     *
     * @param FactureAnnulee $facturesAnnulee
     *
     * @return InterventionDemande
     */
    public function addFacturesAnnulee(FactureAnnulee $facturesAnnulee)
    {
        $this->facturesAnnulees[] = $facturesAnnulee;

        return $this;
    }

    /**
     * Remove facturesAnnulee.
     *
     * @param FactureAnnulee $facturesAnnulee
     */
    public function removeFacturesAnnulee(FactureAnnulee $facturesAnnulee)
    {
        $this->facturesAnnulees->removeElement($facturesAnnulee);
    }

    /**
     * Get facturesAnnulees.
     *
     * @return Collection
     */
    public function getFacturesAnnulees()
    {
        return $this->facturesAnnulees;
    }
}
