<?php

namespace HopitalNumerique\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nodevo\RoleBundle\Entity\Role;
use Nodevo\ToolsBundle\Tools\Chaine;
use Gedmo\Mapping\Annotation as Gedmo;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\Collection;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use Dmishh\SettingsBundle\Entity\SettingsOwnerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\QuestionnaireBundle\Entity\Occurrence;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Inscription;

/**
 * User.
 *
 * @ORM\Table("core_user")
 * @ORM\Entity(repositoryClass="HopitalNumerique\UserBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields="email", message="Cette adresse email existe déjà.")
 * @UniqueEntity(fields="username", message="Ce nom de compte existe déjà.")
 * @Gedmo\Loggable
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="username",
 *          column=@ORM\Column(
 *              name     = "usr_username",
 *              type     = "string",
 *              length   = 50
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="usernameCanonical",
 *          column=@ORM\Column(
 *              name     = "usr_username_canonical",
 *              type     = "string",
 *              length   = 50,
 *              unique   = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="email",
 *          column=@ORM\Column(
 *              name     = "usr_email",
 *              type     = "string",
 *              length   = 50
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="emailCanonical",
 *          column=@ORM\Column(
 *              name     = "usr_email_canonical",
 *              type     = "string",
 *              length   = 50,
 *              unique   = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="enabled",
 *          column=@ORM\Column(
 *              name     = "usr_enabled",
 *              type     = "boolean"
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="salt",
 *          column=@ORM\Column(
 *              name     = "usr_salt",
 *              type     = "string",
 *              length   = 100,
 *              nullable = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="password",
 *          column=@ORM\Column(
 *              name     = "usr_password",
 *              type     = "string",
 *              length   = 100
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="lastLogin",
 *          column=@ORM\Column(
 *              name     = "usr_last_login",
 *              type     = "datetime",
 *              nullable = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="confirmationToken",
 *          column=@ORM\Column(
 *              name     = "usr_confirmation_token",
 *              type     = "string",
 *              length   = 50,
 *              nullable = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="passwordRequestedAt",
 *          column=@ORM\Column(
 *              name     = "usr_password_requested_at",
 *              type     = "datetime",
 *              nullable = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="locked",
 *          column=@ORM\Column(
 *              name     = "usr_locked",
 *              type     = "boolean",
 *              nullable = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="expired",
 *          column=@ORM\Column(
 *              name     = "usr_expired",
 *              type     = "boolean",
 *              nullable = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="expiresAt",
 *          column=@ORM\Column(
 *              name     = "usr_expires_at",
 *              type     = "datetime",
 *              nullable = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="credentialsExpired",
 *          column=@ORM\Column(
 *              name     = "usr_credentials_expired",
 *              type     = "boolean",
 *              nullable = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="credentialsExpireAt",
 *          column=@ORM\Column(
 *              name     = "usr_credentials_expire_at",
 *              type     = "datetime",
 *              nullable = true
 *          )
 *      )
 * })
 */
class User extends BaseUser implements SettingsOwnerInterface
{
    /**
     * @var int ID de l'état Actif
     */
    const ETAT_ACTIF_ID = 3;

    /**
     * @var int ID de l'état Inactif
     */
    const ETAT_INACTIF_ID = 4;

    /**
     * Tableau des rôles pour lesquels on vérifie si les users sont à jour ou pas.
     *
     * @return array
     */
    public static function getRolesContractualisationUpToDate()
    {
        return [
            'ROLE_AMBASSADEUR_7',
            'ROLE_EXPERT_6',
        ];
    }

    /**
     * @var int
     *
     * @ORM\Column(name="usr_id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="usr_date_inscription", type="datetime")
     */
    protected $registrationDate;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Le nom de compte ne peut pas être vide.")
     * @Assert\Regex(pattern= "/[0-9a-zA-Z]/")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom de compte.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom de compte."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[50]],custom[onlyLetterNumber]")
     * @Gedmo\Versioned
     */
    protected $username;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var string
     *
     * @Assert\Regex(pattern= "/[0-9a-zA-Z]/")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le pseudonyme.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le pseudonyme."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[50]],custom[onlyLetterNumber]")
     * @ORM\Column(name="usr_pseudonyme_forum", type="string", length=14, nullable=true)
     * @Gedmo\Versioned
     */
    protected $pseudonym;

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
     * @Gedmo\Versioned
     */
    protected $email;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *      max = "50",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @Nodevo\Javascript(class="validate[required,maxSize[50]]")
     * @ORM\Column(name="usr_nom", type="string", length=50)
     * @Gedmo\Versioned
     */
    protected $lastname;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *      max = "50",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le prénom."
     * )
     * @Nodevo\Javascript(class="validate[required,maxSize[50]]")
     * @ORM\Column(name="usr_prenom", type="string", length=50)
     * @Gedmo\Versioned
     */
    protected $firstname;

    /**
     * @var int
     *
     * @ORM\Column(name="usr_nb_visite", type="integer")
     */
    protected $visitCount;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_region", referencedColumnName="ref_id")
     * @Gedmo\Versioned
     *
     * @GRID\Column(field="region.libelle")
     */
    protected $region;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_user_region",
     *      joinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")}
     * )
     */
    private $rattachementRegions;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_departement", referencedColumnName="ref_id")
     * @Gedmo\Versioned
     */
    protected $county;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="L'état ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @Gedmo\Versioned
     */
    protected $etat;

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
     * @Gedmo\Versioned
     * @ORM\Column(name="usr_telephone_direct", type="string", length=14, nullable=true)
     */
    protected $phoneNumber;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = "14",
     *      max = "14",
     *      minMessage="Le numéro de téléphone portable doit être composé de {{ limit }} caractères.",
     *      maxMessage="Le numéro de téléphone portable doit être composé de {{ limit }} caractères."
     * )
     * @Nodevo\Javascript(class="validate[minSize[14],maxSize[14]],custom[phone]", mask="99 99 99 99 99")
     * @Gedmo\Versioned
     * @ORM\Column(name="usr_telephone_portable", type="string", length=14, nullable=true)
     */
    protected $cellPhoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="usr_contact_autre", type="text", nullable=true)
     * @Gedmo\Versioned
     */
    protected $otherContact;

    /**
     * @ORM\OneToMany(targetEntity="ConnaissanceAmbassadeur", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $connaissancesAmbassadeurs;

    /**
     * @ORM\OneToMany(targetEntity="ConnaissanceAmbassadeurSI", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $connaissancesAmbassadeursSI;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut_etablissement_sante", referencedColumnName="ref_id")
     * @Gedmo\Versioned
     */
    protected $organizationType;

    /**
     * @var Etablissement
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\EtablissementBundle\Entity\Etablissement", inversedBy="usersRattachement", cascade={"persist"})
     * @ORM\JoinColumn(name="eta_etablissement_rattachement_sante", referencedColumnName="eta_id")
     * @Gedmo\Versioned
     */
    protected $organization;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinTable(name="hn_user_type_activite",
     *      joinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")}
     * )
     */
    protected $activities;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le Nom de votre structure si non
     *      disponible dans la liste précédente.", maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans
     *      le Nom de votre structure si non disponible dans la liste précédente."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @Gedmo\Versioned
     * @ORM\Column(name="usr_autre_rattachement_sante", type="string", length=255, nullable=true)
     */
    protected $organizationLabel;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = "3",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le Nom de votre structure si non
     *      disponible dans la liste précédente.", maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans
     *      le Nom de votre structure si non disponible dans la liste précédente."
     * )
     * @Nodevo\Javascript(class="validate[minSize[3],maxSize[255]]")
     * @Gedmo\Versioned
     * @ORM\Column(name="usr_fonction_dans_etablissement", type="string", length=255, nullable=true)
     */
    protected $jobLabel;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_fonction_etablissement_sante", referencedColumnName="ref_id")
     */
    protected $jobType;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_profil_etablissement_sante", referencedColumnName="ref_id")
     * @Gedmo\Versioned
     */
    protected $profileType;

    /**
     * @TODO: à supprimer après la migration de usr_nom_structure vers usr_autre_rattachement_sante
     * @var string
     *
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom de la structure.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom de la structure."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="usr_nom_structure", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    protected $nomStructure;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans la fonction de la structure.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans la fonction de la structure."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="usr_fonction_strucutre", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    protected $fonctionStructure;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Objet", mappedBy="ambassadeurs")
     */
    protected $objets;

    /**
     * @ORM\OneToMany(targetEntity="Contractualisation", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $contractualisations;

    /**
     * @var bool
     *
     * @ORM\Column(name="usr_lock", type="boolean")
     */
    protected $lock;

    /**
     * @var bool
     *
     * @ORM\Column(name="usr_archiver", type="boolean")
     * @Gedmo\Versioned
     */
    protected $archiver;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\QuestionnaireBundle\Entity\Reponse", mappedBy="user", cascade={"persist","remove"})
     */
    protected $reponses;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\QuestionnaireBundle\Entity\Occurrence", mappedBy="user")
     */
    private $questionnaireOccurrences;

    /**
     * @ORM\OneToMany(targetEntity="RefusCandidature", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $refusCandidature;

    /**
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $termsAccepted;

    /**
     * @var string
     *
     * @ORM\Column(name="usr_raison_desinscription", nullable=true, type="text")
     * @Gedmo\Versioned
     */
    protected $raisonDesinscription;

    /**
     * @var string
     *
     * @ORM\Column(name="usr_remarque", nullable=true, type="text")
     * @Gedmo\Versioned
     */
    protected $remarque;

    /**
     * @Assert\File(
     *     maxSize = "200k",
     *     mimeTypes = {
     *         "image/gif",
     *         "image/jpeg",
     *         "image/png",
     *     },
     *     mimeTypesMessage = "Choisissez une image"
     * )
     */
    protected $file;

    /**
     * @var string
     *
     * @ORM\Column(name="usr_photo", type="string", length=255, nullable=true)
     *
     * @Gedmo\Versioned
     */
    protected $path;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="usr_date_derniere_maj", type="datetime")
     */
    protected $dateLastUpdate;

    /**
     * @var string
     *
     * @ORM\Column(name="usr_dashboard_front", type="text", nullable=true)
     */
    protected $dashboardFront;

    /**
     * @var string
     *
     * @ORM\Column(name="usr_dashboard_back", type="text", nullable=true)
     */
    protected $dashboardBack;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionDemande", mappedBy="referent",
                                                                                                     cascade={"persist",
                                                                                                     "remove" })
     */
    protected $interventionDemandesReferent;
    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionDemande", mappedBy="ambassadeur",
                                                                                                     cascade={"persist",
                                                                                                     "remove" })
     */
    protected $interventionDemandesAmbassadeur;
    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionDemande", mappedBy="cmsi",
                                                                                                     cascade={"persist",
                                                                                                     "remove" })
     */
    protected $interventionDemandesCmsi;
    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionDemande", mappedBy="directeur",
                                                                                                     cascade={"persist",
                                                                                                     "remove" })
     */
    protected $interventionDemandesDirecteur;

    /**
     * @ORM\Column(name="usr_last_ip_connection", type="text", nullable=true)
     */
    protected $ipLastConnection;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"}, inversedBy="users")
     * @ORM\JoinTable(name="hn_domaine_gestions_user", joinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")}, inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")})
     */
    protected $domaines;

    /**
     * @var bool
     *
     * @ORM\Column(name="usr_already_be_ambassadeur", type="boolean")
     */
    protected $alreadyBeAmbassadeur;

    /**
     * @var bool
     *
     * @ORM\Column(name="usr_already_be_expert", type="boolean")
     */
    protected $alreadyBeExpert;

    /**
     * @var bool
     *
     * @ORM\Column(name="usr_notification_requete", type="boolean")
     */
    protected $notficationRequete;

    /**
     * @var bool
     *
     * @ORM\Column(name="usr_activity_newsletter_enabled", type="boolean", options={"default"=true})
     */
    protected $activityNewsletterEnabled = true;

    /* <-- Communauté de pratique */

    /**
     * @var bool
     *
     * @Assert\NotNull()
     * @ORM\Column(name="usr_inscrit_communaute_pratique", type="boolean", options={"default"=false})
     */
    private $inscritCommunautePratique;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $communautePratiqueEnrollmentDate;

    /**
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe", mappedBy="animateurs", cascade={"persist", "remove"})
     */
    private $communautePratiqueAnimateurGroupes;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Inscription", mappedBy="user", cascade={"persist", "remove"})
     */
    private $groupeInscription;

    /**
     * @var ArrayCollection
     */
    private $communautePratiqueGroupes;

    /**
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Document", mappedBy="user")
     */
    private $communautePratiqueDocuments;

    /**
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche", mappedBy="user")
     */
    private $communautePratiqueFiches;

    /**
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire", mappedBy="user")
     */
    private $communautePratiqueCommentaires;

    /* --> */

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\ModuleBundle\Entity\Inscription", mappedBy="user")
     */
    private $inscriptions;

    /**
     * @var Reference[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinTable(name="hn_user_computer_skill",
     *      joinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")}
     * )
     */
    private $computerSkills;

    /**
     * @var string
     *
     * @ORM\Column(name="usr_presentation", type="text", nullable=true)
     */
    private $presentation;

    /**
     * @var Reference\Hobby[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference\Hobby", cascade={"persist"})
     * @ORM\JoinTable(name="hn_user_hobby",
     *      joinColumns={@ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="hob_id", referencedColumnName="hob_id", onDelete="CASCADE")}
     * )
     *
     * @Assert\Valid()
     */
    private $hobbies;

    /**
     * @var string
     *
     * @Assert\Regex(pattern="((?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,})", message="Le mot de passe doit comporter au moins 6 caractères et être composé d'au moins une lettre minuscule, d'une lettre majuscule et d'un chiffre.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $plainPassword;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->objets = new ArrayCollection();
        $this->communautePratiqueGroupes = new ArrayCollection();
        $this->groupeInscription = new ArrayCollection();
        $this->communautePratiqueDocuments = new ArrayCollection();
        $this->communautePratiqueFiches = new ArrayCollection();
        $this->username = '';
        $this->pseudonym = '';
        $this->enabled = 1;
        $this->lock = false;
        $this->archiver = false;
        $this->alreadyBeAmbassadeur = false;
        $this->alreadyBeExpert = false;
        $this->visitCount = 0;
        $this->notficationRequete = true;
        $this->inscritCommunautePratique = false;
        $this->previousAdmin = false;
        $this->activities = new ArrayCollection();
        $this->computerSkills = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * @param string $confirmationToken
     *
     * @return $this
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->id;
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
     * Set registrationDate.
     *
     * @param \DateTime $registrationDate
     *
     * @return User
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * Get registrationDate.
     *
     * @return \DateTime
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * Get registrationDate string.
     *
     * @return string
     */
    public function getRegistrationDateString()
    {
        return $this->registrationDate->format('d/m/Y');
    }

    /**
     * Get lastLogin string.
     *
     * @return string
     */
    public function getLastLoginString()
    {
        return $this->lastLogin ? $this->lastLogin->format('d/m/Y') : '';
    }

    /**
     * Get pseudonym.
     *
     * @return string $pseudonym
     */
    public function getPseudonym()
    {
        return $this->pseudonym;
    }

    /**
     * Set pseudonym.
     *
     * @param string $pseudonym
     *
     * @return User
     */
    public function setPseudonym($pseudonym)
    {
        $this->pseudonym = $pseudonym;

        return $this;
    }

    /**
     * Get lastname.
     *
     * @return string $lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set lastname.
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get firstname.
     *
     * @return string $firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set firstname.
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        $this->setUsername($email);

        return $this;
    }

    /**
     * Get visitCount.
     *
     * @return int $visitCount
     */
    public function getVisitCount()
    {
        return $this->visitCount;
    }

    /**
     * Add visitCount.
     *
     * @return User
     */
    public function addvisitCount()
    {
        ++$this->visitCount;

        return $this;
    }

    /**
     * Get region.
     *
     * @return Reference $region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set region.
     *
     * @param Reference $region
     *
     * @return User
     */
    public function setRegion($region)
    {
        if (null === $this->organization) {
            if ($region instanceof Reference) {
                $this->region = $region;
            } else {
                $this->region = null;
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isRegionDom()
    {
        if (null !== $this->getRegion()) {
            return in_array($this->getRegion()->getId(), Reference::DOMRegionsIds());
        }
        return false;
    }

    /**
     * Add rattachementRegions.
     *
     * @param Reference $rattachementRegions
     *
     * @return User
     */
    public function addRattachementRegion(Reference $rattachementRegions)
    {
        $this->rattachementRegions[] = $rattachementRegions;

        return $this;
    }

    /**
     * Remove rattachementRegions.
     *
     * @param Reference $rattachementRegions
     *
     * @return User
     */
    public function removeRattachementRegion(Reference $rattachementRegions)
    {
        $this->rattachementRegions->removeElement($rattachementRegions);

        return $this;
    }

    /**
     * Get rattachementRegions.
     *
     * @return Collection
     */
    public function getRattachementRegions()
    {
        return $this->rattachementRegions;
    }

    /**
     * Has rattachementRegions ?
     *
     * @param Reference $rattachementRegion
     *
     * @return bool
     */
    public function hasRattachementRegion(Reference $rattachementRegion)
    {
        foreach ($this->rattachementRegions as $region) {
            if ($region->getId() === $rattachementRegion->getId()) {
                return true;
            }
        }

        return false;
    }

    /*--- Gestion domaine ---*/

    /**
     * Get domaine.
     *
     * @return string $domaine
     */
    public function getDomainesString()
    {
        $domaineString = '';

        if (null === $this->domaines) {
            return $domaineString;
        }

        foreach ($this->domaines as $domaine) {
            $domaineString .= ($domaineString != '' ? ' | ' : ' ') . $domaine->getNom();
        }

        return $domaineString;
    }

    /**
     * Get les ids des domaines concerné par l'user.
     *
     * @return array[integer]
     */
    public function getDomainesId()
    {
        $domainesId = [];

        if (null === $this->domaines) {
            return $domainesId;
        }

        foreach ($this->domaines as $domaine) {
            $domainesId[] = $domaine->getId();
        }

        return $domainesId;
    }

    /**
     * Add domaine.
     *
     * @param Domaine $domaine
     *
     * @return User
     */
    public function addDomaine(Domaine $domaine)
    {
        $this->domaines[] = $domaine;

        return $this;
    }

    /**
     * Remove domaine.
     *
     * @param Domaine $domaine
     */
    public function removeDomaine(Domaine $domaine)
    {
        $this->domaines->removeElement($domaine);
    }

    /**
     * Set domaines.
     *
     * @param Collection $domaines
     *
     * @return User
     */
    public function setDomaines($domaines)
    {
        $this->domaines = $domaines;

        return $this;
    }

    /**
     * Get domaines.
     *
     * @return Collection|Domaine[]
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Retourne si l'utilisateur possède ce domaine.
     *
     * @param Domaine $domaine Domaine
     *
     * @return bool Si domaine
     */
    public function hasDomaine(Domaine $domaine)
    {
        foreach ($this->domaines as $userDomaine) {
            if ($userDomaine->getId() === $domaine->getId()) {
                return true;
            }
        }

        return false;
    }

    /*-- Fin gestion domaine --*/

    /**
     * Get county.
     *
     * @return Reference $county
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set county.
     *
     * @param Reference $county
     */
    public function setCounty($county)
    {
        if (null === $this->organization) {
            if ($county instanceof Reference) {
                $this->county = $county;
            } else {
                $this->county = null;
            }
        }
    }

    /**
     * Get etat.
     *
     * @return Reference $etat
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set etat.
     *
     * @param Reference $etat
     *
     * @return User
     */
    public function setEtat($etat)
    {
        if ($etat instanceof Reference) {
            $this->etat = $etat;
        } else {
            $this->etat = null;
        }

        return $this;
    }

    /**
     * Get phoneNumber.
     *
     * @return string $phoneNumber
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set phoneNumber.
     *
     * @param string $phoneNumber
     *
     * @return User
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get cellPhoneNumber.
     *
     * @return string $cellPhoneNumber
     */
    public function getCellPhoneNumber()
    {
        return $this->cellPhoneNumber;
    }

    /**
     * Set cellPhoneNumber.
     *
     * @param string $cellPhoneNumber
     *
     * @return User
     */
    public function setCellPhoneNumber($cellPhoneNumber)
    {
        $this->cellPhoneNumber = $cellPhoneNumber;

        return $this;
    }

    /**
     * Get otherContact.
     *
     * @return string $otherContact
     */
    public function getOtherContact()
    {
        return $this->otherContact;
    }

    /**
     * Set otherContact.
     *
     * @param string $otherContact
     *
     * @return User
     */
    public function setOtherContact($otherContact)
    {
        $this->otherContact = $otherContact;

        return $this;
    }

    /**
     * Set activities.
     *
     * @param Reference[] $activities
     *
     * @return User
     */
    public function setActivities($activities)
    {
        $this->activities = new ArrayCollection();

        if (is_array($activities) || $activities instanceof ArrayCollection) {
            foreach ($activities as $activity) {
                $this->addActivity($activity);
            }
        } elseif ($activities instanceof Reference) {
            $this->addActivity($activities);
        }

        return $this;
    }

    /**
     * Add activities.
     * @param Reference[] $activities
     *
     * @return User
     */
    public function addActivities($activities)
    {
        if (is_array($activities) || $activities instanceof ArrayCollection) {
            foreach ($activities as $activity) {
                $this->addActivity($activity);
            }
        } elseif ($activities instanceof Reference) {
            $this->addActivity($activities);
        }

        return $this;
    }

    /**
     * Add activity.
     *
     * @param Reference $activity
     *
     * @return $this
     */
    public function addActivity(Reference $activity)
    {
        $this->activities->add($activity);

        return $this;
    }

    /**
     * Get activities.
     *
     * @return ArrayCollection $activities
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * Get activities string
     *
     * @return string
     */
    public function getActivitiesString()
    {
        return implode(', ', array_map(function ($activity) {
                return $activity->getLibelle();
        }, $this->activities->toArray()));
    }

    /**
     * Retourne si l'utilisateur possède tel type d'activité.
     *
     * @param Reference $activity
     *
     * @return bool Si possède
     */
    public function hasActivity(Reference $activity)
    {
        foreach ($this->activities as $existingActiviteType) {
            if ($activity->equals($existingActiviteType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne si l'utilisateur possède exactement ces types d'activité.
     *
     * @param array <\HopitalNumerique\ReferenceBundle\Entity\Reference> $activiteTypes Types d'activité
     *
     * @return bool Si possède
     */
    public function equalsActivities(array $activiteTypes)
    {
        if (count($this->activities) === count($activiteTypes)) {
            foreach ($activiteTypes as $activiteType) {
                if (!$this->hasActivity($activiteType)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Set organizationType.
     *
     * @param Reference $organizationType
     *
     * @return User
     */
    public function setOrganizationType($organizationType)
    {
        if ($organizationType instanceof Reference && null === $this->organization) {
            $this->organizationType = $organizationType;
        } else {
            $this->organizationType = null;
        }

        return $this;
    }

    /**
     * Get organizationType.
     *
     * @return Reference $organizationType
     */
    public function getOrganizationType()
    {
        return $this->organizationType;
    }

    /**
     * Get organization.
     *
     * @return Etablissement
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Get OrganizationString.
     *
     * @return string|null
     */
    public function getOrganizationString()
    {
        if (is_object($this->organization)) {
            return $this->organization->getNom();
        }

        return null;
    }

    /**
     * Set organization.
     *
     * @param Etablissement|null $organization
     */
    public function setOrganization(Etablissement $organization = null)
    {
        $this->organization = $organization;

        if (null !== $organization) {
            $this->region = $organization->getRegion();
            $this->county = $organization->getDepartement();
            $this->organizationType = $organization->getTypeOrganisme();
            $this->organizationLabel = null;
        }
    }

    /**
     * Get organizationLabel.
     *
     * @return string $organizationLabel
     */
    public function getOrganizationLabel()
    {
        return $this->organizationLabel;
    }

    /**
     * Retourne le nom de l'établissement de l'utilisateur
     * (celui de la liste ou celui qu'il a saisi).
     *
     * @return string
     */
    public function getNomEtablissement()
    {
        if ($this->getOrganizationString() != null) {
            return $this->getOrganizationString();
        } elseif ($this->getOrganizationLabel() != null) {
            return $this->getOrganizationLabel();
        }
    }

    /**
     * Set organizationLabel.
     *
     * @param string $organizationLabel
     */
    public function setOrganizationLabel($organizationLabel)
    {
        if (null === $this->organization) {
            $this->organizationLabel = $organizationLabel;
        }
    }

    /**
     * Get jobLabel.
     *
     * @return string $jobLabel
     */
    public function getJobLabel()
    {
        return $this->jobLabel;
    }

    /**
     * Set jobLabel.
     *
     * @param string $jobLabel
     */
    public function setJobLabel($jobLabel)
    {
        $this->jobLabel = $jobLabel;
    }

    /**
     * Set jobType.
     *
     * @param Reference $jobType
     */
    public function setJobType($jobType)
    {
        $this->jobType = $jobType instanceof Reference ? $jobType : null;
    }

    /**
     * Get jobType.
     *
     * @return Reference $jobType
     */
    public function getJobType()
    {
        return $this->jobType;
    }

    /**
     * Set profileType.
     *
     * @param Reference $profileType
     */
    public function setProfileType($profileType)
    {
        $this->profileType = $profileType instanceof Reference ? $profileType : null;
    }

    /**
     * Get profileType.
     *
     * @return Reference $profileType
     */
    public function getProfileType()
    {
        return $this->profileType;
    }

    /**
     * Get nomStructure.
     *
     * @return string $nomStructure
     */
    public function getNomStructure()
    {
        return $this->nomStructure;
    }

    /**
     * Set nomStructure.
     *
     * @param string $nomStructure
     */
    public function setNomStructure($nomStructure)
    {
        $this->nomStructure = $nomStructure;
    }

    /**
     * Get fonctionStructure.
     *
     * @deprecated Use getJobLabel instead.
     *
     * @return string $fonctionStructure
     */
    public function getFonctionStructure()
    {
        return $this->fonctionStructure;
    }

    /**
     * Set fonctionStructure.
     *
     * @deprecated Use setJobLabel instead.
     *
     * @param string $fonctionStructure
     */
    public function setFonctionStructure($fonctionStructure)
    {
        $this->fonctionStructure = $fonctionStructure;
    }

    /**
     * Get lock.
     *
     * @return bool $lock
     */
    public function getLock()
    {
        return $this->lock;
    }

    /**
     * Set lock.
     *
     * @param bool $lock
     */
    public function setLock($lock)
    {
        $this->lock = $lock;
    }

    /**
     * Get notficationRequete.
     *
     * @return bool $notficationRequete
     */
    public function getNotficationRequete()
    {
        return $this->notficationRequete;
    }

    /**
     * Set notficationRequete.
     *
     * @param bool $notficationRequete
     */
    public function setNotficationRequete($notficationRequete)
    {
        $this->notficationRequete = $notficationRequete;
    }

    /**
     * @return bool
     */
    public function isActivityNewsletterEnabled()
    {
        return $this->activityNewsletterEnabled;
    }

    /**
     * @param bool $activityNewsletterEnabled
     *
     * @return User
     */
    public function setActivityNewsletterEnabled($activityNewsletterEnabled)
    {
        $this->activityNewsletterEnabled = $activityNewsletterEnabled;

        return $this;
    }

    /**
     * Get archiver.
     *
     * @return bool $archiver
     */
    public function getArchiver()
    {
        return $this->archiver;
    }

    /**
     * Set archiver.
     *
     * @param bool $archiver
     */
    public function setArchiver($archiver)
    {
        $this->archiver = $archiver;
    }

    /**
     * Get alreadyBeAmbassadeur.
     *
     * @return bool $alreadyBeAmbassadeur
     */
    public function getAlreadyBeAmbassadeur()
    {
        return $this->alreadyBeAmbassadeur;
    }

    /**
     * Set alreadyBeAmbassadeur.
     *
     * @param bool $alreadyBeAmbassadeur
     */
    public function setAlreadyBeAmbassadeur($alreadyBeAmbassadeur)
    {
        $this->alreadyBeAmbassadeur = $alreadyBeAmbassadeur;
    }

    /**
     * Get alreadyBeExpert.
     *
     * @return bool $alreadyBeExpert
     */
    public function getAlreadyBeExpert()
    {
        return $this->alreadyBeExpert;
    }

    /**
     * Set alreadyBeExpert.
     *
     * @param bool $alreadyBeExpert
     */
    public function setAlreadyBeExpert($alreadyBeExpert)
    {
        $this->alreadyBeExpert = $alreadyBeExpert;
    }

    /**
     * Add Contractualisation.
     *
     * @param Contractualisation $contractualisations
     *
     * @return User
     */
    public function addContractualisation(Contractualisation $contractualisations)
    {
        $this->contractualisations[] = $contractualisations;

        return $this;
    }

    /**
     * Remove Contractualisation.
     *
     * @param Contractualisation $contractualisations
     */
    public function removeContractualisation(Contractualisation $contractualisations)
    {
        $this->contractualisations->removeElement($contractualisations);
    }

    /**
     * Get contractualisations.
     *
     * @return Collection
     */
    public function getContractualisations()
    {
        return $this->contractualisations;
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

    // ^ -------- Gestion questionnaire  -------- ^

    /**
     * Add reponses.
     *
     * @param Reponse $reponses
     *
     * @return User
     */
    public function addReponse(Reponse $reponses)
    {
        $this->reponses[] = $reponses;

        return $this;
    }

    /**
     * Remove reponses.
     *
     * @param Reponse $reponses
     */
    public function removeReponse(Reponse $reponses)
    {
        $this->reponses->removeElement($reponses);
    }

    /**
     * Get reponses.
     *
     * @return Collection
     */
    public function getReponses()
    {
        return $this->reponses;
    }

    /**
     * Add questionnaireOccurrences.
     *
     * @param Occurrence $questionnaireOccurrences
     *
     * @return User
     */
    public function addQuestionnaireOccurrence(Occurrence $questionnaireOccurrences)
    {
        $this->questionnaireOccurrences[] = $questionnaireOccurrences;

        return $this;
    }

    /**
     * Remove questionnaireOccurrences.
     *
     * @param Occurrence $questionnaireOccurrences
     */
    public function removeQuestionnaireOccurrence(Occurrence $questionnaireOccurrences)
    {
        $this->questionnaireOccurrences->removeElement($questionnaireOccurrences);
    }

    /**
     * Get questionnaireOccurrences.
     *
     * @return Collection
     */
    public function getQuestionnaireOccurrences()
    {
        return $this->questionnaireOccurrences;
    }

    // v -------- Gestion questionnaire  -------- v

    /**
     * @return mixed
     */
    public function getTermsAccepted()
    {
        return $this->termsAccepted;
    }

    /**
     * @param $termsAccepted
     */
    public function setTermsAccepted($termsAccepted)
    {
        $this->termsAccepted = (bool) $termsAccepted;
    }

    /**
     * Retourne le prénom puis le lastname.
     *
     * @return string
     */
    public function getPrenomNom()
    {
        return ucfirst($this->firstname) . ' ' . ucfirst($this->lastname);
    }

    /**
     * Retourne le lastname puis le prénom.
     *
     * @return string
     */
    public function getNomPrenom()
    {
        return ucfirst($this->lastname) . ' ' . ucfirst($this->firstname);
    }

    /**
     * Retourne si l'utilisateur a le rôle Admin ou pas.
     *
     * @return bool VRAI si admin
     */
    public function hasRoleAdmin()
    {
        return $this->hasRole(Role::$ROLE_ADMIN_LABEL);
    }

    /**
     * @return bool
     */
    public function hasRoleCDPAdmin()
    {
        foreach ($this->getRoles() as $role) {
            if (in_array($role, [
                Role::$ROLE_ADMIN_LABEL,
                Role::$ROLE_ADMIN_HN_LABEL,
                Role::$ROLE_ADMIN_DOMAINE,
            ])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasRoleAdminHn()
    {
        return $this->hasRole(Role::$ROLE_ADMIN_HN_LABEL);
    }

    /**
     * @return bool
     */
    public function hasRoleAdminDomaine()
    {
        return $this->hasRole(Role::$ROLE_ADMIN_DOMAINE);
    }

    /**
     * @return bool
     */
    public function hasRoleAdminAutodiag()
    {
        return $this->hasRole(Role::$ROLE_ADMIN_AUTODIAG);
    }

    /**
     * Retourne si l'utilisateur a le rôle CMSI ou pas.
     *
     * @return bool VRAI ssi l'utilisateur a le rôle CMSI
     */
    public function hasRoleCmsi()
    {
        return $this->hasRole(Role::$ROLE_CMSI_LABEL);
    }

    /**
     * Retourne si l'utilisateur a le rôle ES - Direction générale ou pas.
     *
     * @return bool VRAI ssi l'utilisateur a le rôle ES - Direction générale
     */
    public function hasRoleDirecteur()
    {
        return $this->hasRole(Role::$ROLE_DIRECTEUR_LABEL);
    }

    /**
     * Retourne si l'utilisateur a le rôle ambassadeur ou pas.
     *
     * @return bool VRAI ssi l'utilisateur a le rôle ambassadeur
     */
    public function hasRoleAmbassadeur()
    {
        return $this->hasRole(Role::$ROLE_AMBASSADEUR_LABEL);
    }

    /**
     * Retourne si l'utilisateur a le rôle ES ou pas.
     *
     * @return bool VRAI ssi l'utilisateur a le rôle es
     */
    public function hasRoleEs()
    {
        return $this->hasRole(Role::$ROLE_ES_LABEL);
    }

    /**
     * Retourne si l'utilisateur a le rôle Expert ou pas.
     *
     * @return bool VRAI ssi l'utilisateur a le rôle expert
     */
    public function hasRoleExpert()
    {
        return $this->hasRole(Role::$ROLE_EXPERT_LABEL);
    }

    /**
     * @return bool
     */
    public function hasRoleAnap()
    {
        return $this->hasRole(Role::$ROLE_ANAP_MEMBRE);
    }

    /**
     * Add ConnaissancesAmbassadeur.
     *
     * @param ConnaissanceAmbassadeur $connaissanceAmbassadeur
     *
     * @return User
     */
    public function addConnaissancesAmbassadeur(ConnaissanceAmbassadeur $connaissanceAmbassadeur)
    {
        $this->connaissancesAmbassadeurs[] = $connaissanceAmbassadeur;

        return $this;
    }

    /**
     * Remove ConnaissancesAmbassadeur.
     *
     * @param ConnaissanceAmbassadeur $connaissanceAmbassadeur
     */
    public function removeConnaissancesAmbassadeur(ConnaissanceAmbassadeur $connaissanceAmbassadeur)
    {
        $this->connaissancesAmbassadeurs->removeElement($connaissanceAmbassadeur);
    }

    /**
     * Get connaissanceAmbassadeur.
     *
     * @return Collection|ConnaissanceAmbassadeur
     */
    public function getConnaissancesAmbassadeurs()
    {
        return $this->connaissancesAmbassadeurs;
    }

    /**
     * Get connaissanceAmbassadeurString.
     *
     * @return string liste des connaissances
     */
    public function getConnaissancesAmbassadeursString()
    {
        $ambassadeurString = '';

        if (null === $this->connaissancesAmbassadeurs) {
            return $ambassadeurString;
        }

        /** @var ConnaissanceAmbassadeur $ambassadeur */
        foreach ($this->connaissancesAmbassadeurs as $ambassadeur) {
            if ($ambassadeur->getDomaine()) {
                $ambassadeurString .=
                    ($ambassadeurString != '' ? ' | ' : ' ') . $ambassadeur->getDomaine()->getLibelle()
                ;
            }
        }

        return $ambassadeurString;
    }

    /**
     * Get connaissancesAmbassadeursSI.
     *
     * @return Collection
     */
    public function getConnaissancesAmbassadeursSI()
    {
        return $this->connaissancesAmbassadeursSI;
    }

    /**
     * Retourne les prénom et lastname de l'utilisateur.
     *
     * @return string Appelation de l'utilisateur
     */
    public function getAppellation()
    {
        // ----Traitement pour transformer le prénom "Jean-luc robert" en "Jean-Luc Robert"
        // Récupération du prénom
        $prenom = strtolower($this->getFirstname());
        // Découpage du prénom sur le tiret
        $tempsPrenom = explode('-', $prenom);
        // Unsset de la variable
        $prenom = '';
        // Pour chaque bout on met une MAJ sur la première lettre de chaque mot,
        // s'il y en plusieurs c'est qu'il y avait un -
        foreach ($tempsPrenom as $key => $tempPrenom) {
            $prenom .= ('' !== $prenom) ? ('-' . ucwords($tempPrenom)) : ucwords($tempPrenom);
        }

        // ----Mise en majuscule du lastname
        $nom = strtoupper($this->getLastname());

        return $prenom . ' ' . $nom;
    }

    /**
     * Retourne si l'utilisateur est actif.
     *
     * @return bool vRAI ssi l'utilisateur est actif
     */
    public function isActif()
    {
        return $this->etat != null && $this->etat->getId() == self::ETAT_ACTIF_ID;
    }

    /**
     * Get email.
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->roles[0];
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = parent::getRoles();

        if (in_array('ROLE_ADMINISTRATEUR_1', $roles)) {
            $roles[] = 'ROLE_ADMIN';
            $roles[] = 'ROLE_SUPER_ADMIN';
            $roles[] = 'ROLE_ALLOWED_TO_SWITCH';
        }

        return $roles;
    }

    /**
     * Get raisonDesinscription.
     *
     * @return string $raisonDesinscription
     */
    public function getRaisonDesinscription()
    {
        return $this->raisonDesinscription;
    }

    /**
     * Set raisonDesinscription.
     *
     * @param string $raisonDesinscription
     */
    public function setRaisonDesinscription($raisonDesinscription)
    {
        $this->raisonDesinscription = $raisonDesinscription;
    }

    /**
     * Get remarque.
     *
     * @return string $remarque
     */
    public function getRemarque()
    {
        return $this->remarque;
    }

    /**
     * Set remarque.
     *
     * @param string $remarque
     *
     * @return User
     */
    public function setRemarque($remarque)
    {
        $this->remarque = $remarque;

        return $this;
    }

    /**
     * Get dateLastUpdate.
     *
     * @return \DateTime $dateLastUpdate
     */
    public function getDateLastUpdate()
    {
        return $this->dateLastUpdate;
    }

    /**
     * Set dateLastUpdate.
     *
     * @param \DateTime $dateLastUpdate
     *
     * @return User
     */
    public function setDateLastUpdate($dateLastUpdate)
    {
        $this->dateLastUpdate = $dateLastUpdate;

        return $this;
    }

    /**
     * Get dashboardFront.
     *
     * @return string $dashboardFront
     */
    public function getDashboardFront()
    {
        return $this->dashboardFront;
    }

    /**
     * Set dashboardFront.
     *
     * @param string $dashboardFront
     *
     * @return User
     */
    public function setDashboardFront($dashboardFront)
    {
        $this->dashboardFront = $dashboardFront;

        return $this;
    }

    /**
     * Get dashboardBack.
     *
     * @return string $dashboardBack
     */
    public function getDashboardBack()
    {
        return $this->dashboardBack;
    }

    /**
     * Set dashboardBack.
     *
     * @param string $dashboardBack
     *
     * @return User
     */
    public function setDashboardBack($dashboardBack)
    {
        $this->dashboardBack = $dashboardBack;

        return $this;
    }

    /**
     * @param $role
     *
     * @return bool
     */
    public function isGranted($role)
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return User
     */
    public function setPath($path)
    {
        if (null === $path && file_exists($this->getAbsolutePath())) {
            unlink($this->getAbsolutePath());
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    /**
     * @return null|string
     */
    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    /**
     * @return string
     */
    public function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __WEB_DIRECTORY__ . '/' . $this->getUploadDir();
    }

    /**
     * @return string
     */
    public function getUploadDir()
    {
        return 'medias/Utilisateurs';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            //delete Old File
            if (file_exists($this->getAbsolutePath())) {
                unlink($this->getAbsolutePath());
            }

            $tool = new Chaine($this->getPrenomNom());
            $nomFichier = $tool->minifie();

            $this->path = round(microtime(true) * 1000) . '_' . $nomFichier . '.jpg';
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        $this->file->move($this->getUploadRootDir(), $this->path);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $file = $this->getAbsolutePath();

        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Get Last ip connection.
     *
     * @return string
     */
    public function getIpLastConnection()
    {
        return $this->ipLastConnection;
    }

    /**
     * Set Last ip Connection.
     *
     * @param string $ip
     *
     * @return string
     */
    public function setIpLastConnection($ip)
    {
        $this->ipLastConnection = $ip;

        return $this;
    }

    /**
     * Get inscritCommunautePratique.
     *
     * @return bool $inscritCommunautePratique
     */
    public function isInscritCommunautePratique()
    {
        return $this->inscritCommunautePratique;
    }

    public function getInscritCommunautePratiqueString()
    {
        return $this->inscritCommunautePratique ? 'Oui' : 'Non';
    }

    /**
     * Set inscritCommunautePratique.
     *
     * @param bool $inscritCommunautePratique
     *
     * @return $this
     */
    public function setInscritCommunautePratique($inscritCommunautePratique)
    {
        $this->inscritCommunautePratique = $inscritCommunautePratique;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCommunautePratiqueEnrollmentDate()
    {
        return $this->communautePratiqueEnrollmentDate;
    }

    /**
     * @param \DateTime $communautePratiqueEnrollmentDate
     *
     * @return User
     */
    public function setCommunautePratiqueEnrollmentDate($communautePratiqueEnrollmentDate = null)
    {
        $this->communautePratiqueEnrollmentDate = $communautePratiqueEnrollmentDate;

        return $this;
    }

    /**
     * Add communautePratiqueGroupe.
     *
     * @param Groupe $communautePratiqueGroupe
     *
     * @return User
     */
    public function addCommunautePratiqueAnimateurGroupe(Groupe $communautePratiqueGroupe)
    {
        $this->addCommunautePratiqueAnimateurGroupeInscription(new Inscription($communautePratiqueGroupe, $this));

        return $this;
    }

    /**
     * Add groupeInscription.
     *
     * @param Inscription $groupeInscription
     *
     * @return User
     */
    public function addCommunautePratiqueAnimateurGroupeInscription(Inscription $groupeInscription)
    {
        $groupeInscription->getGroupe()->addAnimateur($this);
        $this->communautePratiqueAnimateurGroupes[] = $groupeInscription->getGroupe();

        return $this;
    }

    /**
     * Remove communautePratiqueGroupes.
     *
     * @param Groupe $communautePratiqueGroupes
     *
     * @return User
     */
    public function removeCommunautePratiqueAnimateurGroupe(Groupe $communautePratiqueGroupes)
    {
        $this->removeCommunautePratiqueAnimateurGroupeInscription(new Inscription($communautePratiqueGroupes, $this));

        return $this;
    }

    /**
     * Remove groupeInscription.
     *
     * @param Inscription $groupeInscription
     *
     * @return User
     */
    public function removeCommunautePratiqueAnimateurGroupeInscription(Inscription $groupeInscription)
    {
        $this->communautePratiqueAnimateurGroupes->removeElement($groupeInscription->getGroupe());
        $groupeInscription->getGroupe()->removeAnimateur($this);

        return $this;
    }

    /**
     * Get communautePratiqueGroupes.
     *
     * @return Collection
     */
    public function getCommunautePratiqueAnimateurGroupes()
    {
        return $this->communautePratiqueAnimateurGroupes;
    }

    /**
     * Add communautePratiqueGroupe.
     *
     * @param Groupe $communautePratiqueGroupe
     *
     * @return User
     */
    public function addCommunautePratiqueGroupe(Groupe $communautePratiqueGroupe, $autoValidate = false)
    {
        $this->addCommunautePratiqueGroupeInscription(new Inscription($communautePratiqueGroupe, $this, $autoValidate));

        return $this;
    }

    /**
     * Add groupeInscription.
     *
     * @param Inscription $groupeInscription
     *
     * @return User
     */
    public function addCommunautePratiqueGroupeInscription(Inscription $groupeInscription)
    {
        $this->groupeInscription[] = $groupeInscription;

        return $this;
    }

    /**
     * is Actif in groupe.
     *
     * @param Groupe $groupe
     *
     * @return bool
     *
     */
    public function isActifInGroupe(Groupe $groupe)
    {
        return $this->isActifGroupeInscription(new Inscription($groupe, $this));
    }

    /**
     * is Actif in groupe.
     *
     * @param Inscription $groupeInscription
     *
     * @return bool
     */
    public function isActifGroupeInscription(Inscription $groupeInscription)
    {
        foreach ($this->groupeInscription->getValues() as $inscrit) {
            if ($inscrit->getGroupe()->getId() == $groupeInscription->getGroupe()->getId()) {
                return $inscrit->isActif();
            }
        }

        return false;
    }

    /**
     * Remove communautePratiqueGroupes.
     *
     * @param Groupe $communautePratiqueGroupes
     */
    public function removeCommunautePratiqueGroupe(Groupe $communautePratiqueGroupes)
    {
        $this->removeCommunautePratiqueGroupeInscription(new Inscription($communautePratiqueGroupes, $this));
    }

    /**
     * Remove groupeInscription.
     *
     * @param Inscription $groupeInscription
     */
    public function removeCommunautePratiqueGroupeInscription(Inscription $groupeInscription)
    {
        $this->groupeInscription->removeElement($groupeInscription);
        $groupeInscription->getGroupe()->removeUser($this);
    }

    /**
     * Has communautePratiqueGroupe ?
     *
     * @param Groupe $communautePratiqueGroupe
     *
     * @return bool
     */
    public function hasCommunautePratiqueGroupe(Groupe $communautePratiqueGroupe)
    {
        return $this->hasCommunautePratiqueGroupeInscription(new Inscription($communautePratiqueGroupe, $this));
    }

    /**
     * @param Groupe $group
     *
     * @return bool
     */
    public function isRegisteredInCDPGroup(Groupe $group)
    {
        foreach ($this->getGroupeInscription() as $registration) {
            if ($registration->isActif() && $registration->getGroupe()->getId() == $group->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Has $groupeInscription ?
     *
     * @param Inscription $groupeInscription
     *
     * @return bool
     */
    public function hasCommunautePratiqueGroupeInscription(Inscription $groupeInscription)
    {
        /** @var Inscription $inscrit */
        foreach ($this->groupeInscription->getValues() as $inscrit) {
            if ($inscrit->getGroupe()->getId() == $groupeInscription->getGroupe()->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get communautePratiqueGroupes.
     *
     * @return Collection
     */
    public function getCommunautePratiqueGroupes()
    {
        if (null === $this->communautePratiqueGroupes) {
            $this->communautePratiqueGroupes = new ArrayCollection();
        }

        foreach ($this->getGroupeInscription() as $inscrit) {
            $this->communautePratiqueGroupes[$inscrit->getGroupe()->getId()] = $inscrit->getGroupe();
        }

        return $this->communautePratiqueGroupes;
    }

    /**
     * Get groupeInscription.
     *
     * @return Collection|Inscription[]
     */
    public function getGroupeInscription()
    {
        return $this->groupeInscription;
    }

    /**
     * Add communautePratiqueDocument.
     *
     * @param Document $communautePratiqueDocument
     *
     * @return User
     */
    public function addCommunautePratiqueDocument(Document $communautePratiqueDocument)
    {
        $this->communautePratiqueDocuments[] = $communautePratiqueDocument;

        return $this;
    }

    /**
     * Remove communautePratiqueDocuments.
     *
     * @param Document $communautePratiqueDocument
     */
    public function removeCommunautePratiqueDocument(Document $communautePratiqueDocument)
    {
        $this->communautePratiqueDocuments->removeElement($communautePratiqueDocument);
    }

    /**
     * Get communautePratiqueDocuments.
     *
     * @return Collection
     */
    public function getCommunautePratiqueDocuments()
    {
        return $this->communautePratiqueDocuments;
    }

    /**
     * Add communautePratiqueFiche.
     *
     * @param Fiche $communautePratiqueFiche
     *
     * @return User
     */
    public function addCommunautePratiqueFiche(Fiche $communautePratiqueFiche)
    {
        $this->communautePratiqueFiches[] = $communautePratiqueFiche;

        return $this;
    }

    /**
     * Remove communautePratiqueFiches.
     *
     * @param Fiche $communautePratiqueFiche
     */
    public function removeCommunautePratiqueFiche(Fiche $communautePratiqueFiche)
    {
        $this->communautePratiqueFiches->removeElement($communautePratiqueFiche);
    }

    /**
     * Get communautePratiqueFiches.
     *
     * @return Collection
     */
    public function getCommunautePratiqueFiches()
    {
        return $this->communautePratiqueFiches;
    }

    /**
     * Add communautePratiqueCommentaire.
     *
     * @param Commentaire $communautePratiqueCommentaire
     *
     * @return User
     */
    public function addCommunautePratiqueCommentaire(Commentaire $communautePratiqueCommentaire)
    {
        $this->communautePratiqueCommentaires[] = $communautePratiqueCommentaire;

        return $this;
    }

    /**
     * Remove communautePratiqueCommentaires.
     *
     * @param Commentaire $communautePratiqueCommentaire
     */
    public function removeCommunautePratiqueCommentaire(Commentaire $communautePratiqueCommentaire)
    {
        $this->communautePratiqueCommentaires->removeElement($communautePratiqueCommentaire);
    }

    /**
     * Get communautePratiqueCommentaires.
     *
     * @return Collection
     */
    public function getCommunautePratiqueCommentaires()
    {
        return $this->communautePratiqueCommentaires;
    }

    /**
     * Equals.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user User
     *
     * @return bool Si égalité
     */
    public function equals(User $user)
    {
        return $this->id === $user->getId();
    }

    /**
     * Retourne l'image de l'avatar à afficher (image générique si aucun avatar).
     *
     * @return string Avatar
     */
    public function getAvatarWebPath()
    {
        if (null !== $this->path) {
            return '/' . $this->getWebPath();
        }

        return '/bundles/hopitalnumeriqueuser/img/default_user.png';
    }

    /**
     * On vérifie que la date de renouvellement de la dernière contractualisation n'est pas dépassée.
     *
     * @return null|string
     */
    public function isUpToDate()
    {
        if (0 === count(array_intersect($this->roles, self::getRolesContractualisationUpToDate()))) {
            return true;
        }

        if (0 === $this->getContractualisations()->count()) {
            return false;
        }

        $dateLimit = (new \DateTime('now'))->add(new \DateInterval('P45D'));
        $archive = 0;

        /** @var Contractualisation $contractualisation */
        foreach ($this->getContractualisations() as $contractualisation) {
            $dateRenew = $contractualisation->getDateRenouvellement();

            if (true === $contractualisation->getArchiver()) {
                ++$archive;
            } elseif ($dateRenew <= $dateLimit && $dateRenew != null) {
                return false;
            }
        }

        return $archive === count($this->getContractualisations()) ? false : true;
    }

    /**
     * @return string
     */
    public function getUpToDateToString()
    {
        return $this->isUpToDate() ? 'Oui' : 'Non';
    }

    /**
     * Retourne la date de renouvellement de la dernière contractualisation enregistrée pour l'utilisateur.
     *
     * @return \DateTime|null
     */
    public function getDateLastContractualisation()
    {
        $lastDateContractualisation = null;

        /** @var Contractualisation $contractualisation */
        foreach ($this->contractualisations as $contractualisation) {
            $lastDateContractualisation = $contractualisation->getDateRenouvellement();
        }

        return $lastDateContractualisation;
    }

    /**
     * @return ArrayCollection
     */
    public function getComputerSkills()
    {
        return $this->computerSkills;
    }

    /**
     * Get computer skills string.
     *
     * @return string
     */
    public function getComputerSkillsString()
    {
        return implode(', ', array_map(function ($skill) {
            return $skill->getLibelle();
        }, $this->computerSkills->toArray()));
    }

    /**
     * @param Reference $skill
     *
     * @return User
     */
    public function addComputerSkills(Reference $skill)
    {
        $this->computerSkills->add($skill);

        return $this;
    }

    /**
     * @return string
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * @param string $presentation
     *
     * @return User
     */
    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;

        return $this;
    }

    /**
     * @return Collection|Reference\Hobby[]
     */
    public function getHobbies()
    {
        return $this->hobbies;
    }

    /**
     * @return string
     */
    public function getHobbiesString()
    {
        return implode(', ', array_map(function ($hobby) {
            return $hobby->getLabel();
        }, $this->hobbies->toArray()));
    }

    /**
     * @param Reference\Hobby $hobby
     *
     * @return User
     */
    public function addHobby(Reference\Hobby $hobby)
    {
        foreach ($this->hobbies as $userHobby) {
            if ($userHobby->getLabel() === $hobby->getLabel()) {
                return $this;
            }
        }

        $this->hobbies->add($hobby);

        return $this;
    }

    /**
     * @param Reference\Hobby $hobby
     *
     * @return $this
     */
    public function removeHobby(Reference\Hobby $hobby)
    {
        $this->hobbies->removeElement($hobby);

        return $this;
    }

    /**
     * @param mixed $file
     *
     * @return User
     */
    public function setFile($file)
    {
        $this->file = $file;
        $this->setDateLastUpdate(new \DateTime());

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getSettingIdentifier()
    {
        return $this->getId();
    }

    /**
     * @param Groupe $group
     *
     * @return bool
     */
    public function isGroupAnimator(Groupe $group)
    {
        foreach ($group->getAnimateurs() as $groupUsers) {
            if ($groupUsers == $this) {
                return true;
            }
        }
        return false;
    }

    /**
     * For CSV export in BO
     *
     * @return string
     */
    public function getEnabledToString()
    {
        return $this->enabled ? 'Oui' : 'Non';
    }
}
