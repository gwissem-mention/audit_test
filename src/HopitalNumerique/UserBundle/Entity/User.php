<?php

namespace HopitalNumerique\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Nodevo\RoleBundle\Entity\Role;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Gedmo\Mapping\Annotation as Gedmo;

//Tools
use \Nodevo\ToolsBundle\Tools\Chaine;

/**
 * User
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
 *              length   = 50,
 *              options  = {"comment" = "Nom utilisateur pour la connexion"}
 *          )
 *      ), 
 *      @ORM\AttributeOverride(name="usernameCanonical",
 *          column=@ORM\Column(
 *              name     = "usr_username_canonical",
 *              type     = "string",
 *              length   = 50,
 *              options  = {"comment" = "Pseudonyme canonique"},
 *              unique   = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="email",
 *          column=@ORM\Column(
 *              name     = "usr_email",
 *              type     = "string",
 *              length   = 50,
 *              options  = {"comment" = "Adresse électronique"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="emailCanonical",
 *          column=@ORM\Column(
 *              name     = "usr_email_canonical",
 *              type     = "string",
 *              length   = 50,
 *              options  = {"comment" = "Adresse électronique canonique"},
 *              unique   = true
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="enabled",
 *          column=@ORM\Column(
 *              name     = "usr_enabled",
 *              type     = "boolean",
 *              options  = {"comment" = "L utilisateur est-il activé ?"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="salt",
 *          column=@ORM\Column(
 *              name     = "usr_salt",
 *              type     = "string",
 *              length   = 100,
 *              nullable = true,
 *              options  = {"comment" = "Grain de sel de chiffrement du mot de passe"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="password",
 *          column=@ORM\Column(
 *              name     = "usr_password",
 *              type     = "string",
 *              length   = 100,
 *              options  = {"comment" = "Mot de passe"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="lastLogin",
 *          column=@ORM\Column(
 *              name     = "usr_last_login",
 *              type     = "datetime",
 *              nullable = true,
 *              options  = {"comment" = "Date de la dernière connexion"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="confirmationToken",
 *          column=@ORM\Column(
 *              name     = "usr_confirmation_token",
 *              type     = "string",
 *              length   = 50,
 *              nullable = true,
 *              options  = {"comment" = "Jeton de confirmation du compte"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="passwordRequestedAt",
 *          column=@ORM\Column(
 *              name     = "usr_password_requested_at",
 *              type     = "datetime",
 *              nullable = true,
 *              options  = {"comment" = "Date de demande du nouveau mot de passe"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="locked",
 *          column=@ORM\Column(
 *              name     = "usr_locked",
 *              type     = "boolean",
 *              nullable = true,
 *              options  = {"comment" = "Verrouillage de l utilisateur ?"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="expired",
 *          column=@ORM\Column(
 *              name     = "usr_expired",
 *              type     = "boolean",
 *              nullable = true,
 *              options  = {"comment" = "L utilisateur est-il activé ?"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="expiresAt",
 *          column=@ORM\Column(
 *              name     = "usr_expires_at",
 *              type     = "datetime",
 *              nullable = true,
 *              options  = {"comment" = "Date d expiration de l utilisateur"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="credentialsExpired",
 *          column=@ORM\Column(
 *              name     = "usr_credentials_expired",
 *              type     = "boolean",
 *              nullable = true,
 *              options  = {"comment" = "Expiration du mot de passe ?"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="credentialsExpireAt",
 *          column=@ORM\Column(
 *              name     = "usr_credentials_expire_at",
 *              type     = "datetime",
 *              nullable = true,
 *              options  = {"comment" = "Date d expiration du mot de passe"}
 *          )
 *      )
 * })
 */
class User extends BaseUser
{
    private static $ETAT_ACTIF_ID = 3;

    /**
     * @ORM\Column(name="usr_id", type="integer", options = {"comment" = "ID de l utilisateur"})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="usr_date_inscription", type="datetime", options = {"comment" = "Date inscription"})
     */
    protected $dateInscription;

    /**
     * @var string
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
     * @Assert\Regex(pattern= "/[0-9a-zA-Z]/")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le pseudonyme.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le pseudonyme."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[50]],custom[onlyLetterNumber]")
     * @ORM\Column(name="usr_pseudonyme_forum", type="string", length=14, nullable=true, options = {"comment" = "Téléphone portable de l utilisateur"})
     * @Gedmo\Versioned
     */
    protected $pseudonymeForum;
    
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
     * @Assert\NotBlank(message="Le nom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[50]]")
     * @ORM\Column(name="usr_nom", type="string", length=50, options = {"comment" = "Nom de l utilisateur"})
     * @Gedmo\Versioned
     */
    protected $nom;
    
    /**
     * @var string
     * @Assert\NotBlank(message="Le prénom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le prénom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le prénom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[50]]")
     * @ORM\Column(name="usr_prenom", type="string", length=50, options = {"comment" = "Prénom de l utilisateur"})
     * @Gedmo\Versioned
     */
    protected $prenom;

    /**
     * @var integer
     * 
     * @ORM\Column(name="usr_nb_visite", type="integer", options = {"comment" = "Nombre de fois où un user est connecté"})     
     */
    protected $nbVisites;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_region", referencedColumnName="ref_id")
     * @Gedmo\Versioned
     * 
     * @GRID\Column(field="region.libelle")
     */
    protected $region;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_departement", referencedColumnName="ref_id")
     * @Gedmo\Versioned
     */
    protected $departement;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="L'état ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @Gedmo\Versioned
     */
    protected $etat;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_titre", referencedColumnName="ref_id")
     * @Gedmo\Versioned
     */
    protected $titre;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_civilite", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="La civilité ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @Gedmo\Versioned
     */
    protected $civilite;
    
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
     * @ORM\Column(name="usr_telephone_direct", type="string", length=14, nullable=true, options = {"comment" = "Téléphone de l utilisateur"})
     */
    protected $telephoneDirect;
    
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
     * @ORM\Column(name="usr_telephone_portable", type="string", length=14, nullable=true, options = {"comment" = "Téléphone portable de l utilisateur"})
     */
    protected $telephonePortable;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="usr_contact_autre", type="text", nullable=true, options = {"comment" = "Autre moyen de contacter l utilsateur"})
     * @Gedmo\Versioned
     */
    protected $contactAutre;
    
    /**
     * @ORM\OneToMany(targetEntity="ConnaissanceAmbassadeur", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $connaissancesAmbassadeurs;
    
    /**
     * @ORM\OneToMany(targetEntity="ConnaissanceAmbassadeurSI", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $connaissancesAmbassadeursSI;

    // ^ -------- Onglet : Vous êtes un établissement de santé -------- ^
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut_etablissement_sante", referencedColumnName="ref_id")
     * @Gedmo\Versioned
     */
    protected $statutEtablissementSante;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\EtablissementBundle\Entity\Etablissement", inversedBy="usersRattachement", cascade={"persist"})
     * @ORM\JoinColumn(name="eta_etablissement_rattachement_sante", referencedColumnName="eta_id")
     * @Gedmo\Versioned
     */
    protected $etablissementRattachementSante;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinTable(name="hn_user_type_activite",
     *      joinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")}
     * )
     */
    protected $typeActivite;
    
    /**
     * @var string
     * 
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans l'Nom de votre établissement si non disponible dans la liste précédente.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans l'Nom de votre établissement si non disponible dans la liste précédente."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @Gedmo\Versioned
     * @ORM\Column(name="usr_autre_rattachement_sante", type="string", length=255, nullable=true, options = {"comment" = "Nom de votre établissement si non disponible dans la liste précédente santé de l utilisateur"})
     */
    protected $autreStructureRattachementSante;
    
    /**
     * @var string
     * 
     * @Assert\Length(
     *      min = "3",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans l'Nom de votre établissement si non disponible dans la liste précédente.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans l'Nom de votre établissement si non disponible dans la liste précédente."
     * )
     * @Nodevo\Javascript(class="validate[minSize[3],maxSize[255]]")
     * @Gedmo\Versioned
     * @ORM\Column(name="usr_fonction_dans_etablissement", type="string", length=255, nullable=true, options = {"comment" = "Fonction dans l etablissement de santé de l utilisateur"})
     */
    protected $fonctionDansEtablissementSante;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_fonction_etablissement_sante", referencedColumnName="ref_id")
     */
    protected $fonctionDansEtablissementSanteReferencement;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_profil_etablissement_sante", referencedColumnName="ref_id")
     * @Gedmo\Versioned
     */
    protected $profilEtablissementSante;

    // v -------- Onglet : Vous êtes un établissement de santé -------- v
    
    // ^ -------- Onglet : Vous êtes une structure autre qu'un établissement de santé  -------- ^

    /**
     * @var string
     * 
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom de la structure.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom de la structure."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="usr_nom_structure", type="string", length=255, nullable=true, options = {"comment" = "Nom de la structure de l utilisateur"})
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
     * @ORM\Column(name="usr_fonction_strucutre", type="string", length=255, nullable=true, options = {"comment" = "Fonction au sein de la structure"})
     * @Gedmo\Versioned
     */
    protected $fonctionStructure;
    
    // v -------- Onglet : Vous êtes une structure autre qu'un établissement de santé  -------- v

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Objet", mappedBy="ambassadeurs")
     */
    protected $objets;
    
    /**
     * @ORM\OneToMany(targetEntity="Contractualisation", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $contractualisations;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usr_lock", type="boolean", options = {"comment" = "L utilisateur est-il verrouillé ?"})
     */
    protected $lock;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usr_archiver", type="boolean", options = {"comment" = "L utilisateur est-il archivé ?"}) 
     * @Gedmo\Versioned
     */
    protected $archiver;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\QuestionnaireBundle\Entity\Reponse", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $reponses;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="HopitalNumerique\QuestionnaireBundle\Entity\Occurrence", mappedBy="user")
     */
    private $questionnaireOccurrences;

    /**
     * @ORM\OneToMany(targetEntity="RefusCandidature", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $refusCandidature;
 
    // ------- Conditions générales d'utilisations ------- 
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

    // ------- Ambassadeurs  -------
    /**
     * @Assert\File(
     *     maxSize = "10M",
     *     mimeTypes = { 
     *         "image/gif", 
     *         "image/jpeg", 
     *         "image/png",
     *     },
     *     mimeTypesMessage = "Choisissez un fichier valide (IMAGE)"
     * )
     */
    public $file;
    
    /**
     * @var string
     *
     * @ORM\Column(name="usr_photo", type="string", length=255, nullable=true, options = {"comment" = "Nom du fichier stocké"})
     * @Gedmo\Versioned
     */
    protected $path;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="usr_date_derniere_maj", type="datetime")
     */
    protected $dateLastUpdate;
    
    // ------- Dashboards
    /**
     * @var string
     * 
     * @ORM\Column(name="usr_dashboard_front", type="text", options = {"comment" = "Dashboard de l utilisateur"}, nullable=true)
     */
    protected $dashboardFront;

    /**
     * @var string
     * 
     * @ORM\Column(name="usr_dashboard_back", type="text", options = {"comment" = "Dashboard admin de l utilisateur"}, nullable=true)
     */
    protected $dashboardBack;
    
    // ------- Interventions -------
    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionDemande", mappedBy="referent", cascade={"persist", "remove" })
     */
    protected $interventionDemandesReferent;
    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionDemande", mappedBy="ambassadeur", cascade={"persist", "remove" })
     */
    protected $interventionDemandesAmbassadeur;
    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionDemande", mappedBy="cmsi", cascade={"persist", "remove" })
     */
    protected $interventionDemandesCmsi;
    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\InterventionBundle\Entity\InterventionDemande", mappedBy="directeur", cascade={"persist", "remove" })
     */
    protected $interventionDemandesDirecteur;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\AutodiagBundle\Entity\Outil", mappedBy="dernierImportUser", cascade={"persist", "remove" })
     */
    protected $autodiagsImportes;

    /**
    * @ORM\Column(name="usr_last_ip_connection", type="text", options = {"comment" = "IP de la dernière connexion de l'utilisateur"}, nullable=true)
    */
    protected $ipLastConnection;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(name="hn_domaine_gestions_user",
     *      joinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    /**
     * @var string
     *
     * @ORM\Column(name="usr_biographie", nullable=true, type="text")
     * @Gedmo\Versioned
     */
    protected $biographie;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usr_already_be_ambassadeur", type="boolean", options = {"comment" = "A deja ete ambassadeur ?"})
     */
    protected $alreadyBeAmbassadeur;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usr_already_be_expert", type="boolean", options = {"comment" = "A deja ete expert ?"})
     */
    protected $alreadyBeExpert;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usr_notification_requete", type="boolean", options = {"comment" = "L utilisateur est notifie par mail des maj des publications ?"})
     */
    protected $notficationRequete;

    /* <-- Communauté de pratiques */

    /**
     * @var boolean
     * 
     * @Assert\NotNull()
     * @ORM\Column(name="usr_inscrit_communaute_pratique", type="boolean", options={"default"=false,"comment"="Indique si l utilisateur est inscrit à la communauté de pratiques"})
     */
    private $inscritCommunautePratique;

    /**
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe", mappedBy="animateurs", cascade={"persist"})
     */
    private $communautePratiqueAnimateurGroupes;

    /**
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe", mappedBy="users", cascade={"persist"})
     */
    private $communautePratiqueGroupes;

    /**
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Document", mappedBy="user")
     */
    private $communautePratiqueDocuments;

    /* --> */


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->objets               = new \Doctrine\Common\Collections\ArrayCollection();
        $this->communautePratiqueGroupes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->username             = '';
        $this->pseudonymeForum      = '';
        $this->enabled              = 1;
        $this->civilite             = array();
        $this->lock                 = false;
        $this->archiver             = false;
        $this->alreadyBeAmbassadeur = false;
        $this->alreadyBeExpert      = false;
        $this->nbVisites            = 0;
        $this->notficationRequete   = true;
        $this->inscritCommunautePratique = false;
        $this->previousAdmin 		= false;
    }


    public function getConfirmationToken()
    {
      return $this->confirmationToken;
    }

    public function setConfirmationToken($confirmationToken)
    {
      $this->confirmationToken = $confirmationToken;

      return $this;
    }

    public function __toString()
    {
        return (string) $this->id;
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
     * Set dateInscription
     *
     * @param \DateTime $dateInscription
     * @return User
     */
    public function setDateInscription($dateInscription)
    {
        $this->dateInscription = $dateInscription;
    
        return $this;
    }
    
    /**
     * Get dateInscription
     *
     * @return \DateTime
     */
    public function getDateInscription()
    {
        return $this->dateInscription;
    }

    /**
     * Get dateInscription string
     *
     * @return string
     */
    public function getDateInscriptionString()
    {
        return $this->dateInscription->format('d/m/Y');
    }

    /**
     * Get lastLogin string
     *
     * @return string
     */
    public function getLastLoginString()
    {
        return $this->lastLogin ? $this->lastLogin->format('d/m/Y') : '';
    }
    
    /**
     * Get pseudonymeForum
     *
     * @return string $pseudonymeForum
     */
    public function getPseudonymeForum()
    {
        return $this->pseudonymeForum;
    }
    
    /**
     * Set pseudonymeForum
     *
     * @param string $pseudonymeForum
     */
    public function setPseudonymeForum($pseudonymeForum)
    {
        $this->pseudonymeForum = $pseudonymeForum;
    }
    
    /**
     * Get nom
     *
     * @return string $nom
     */
    public function getNom()
    {
        return $this->nom;
    }
    
    /**
     * Set nom
     *
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }
    
    /**
     * Get prenom
     *
     * @return string $prenom
     */
    public function getPrenom()
    {
        return $this->prenom;
    }
    
    /**
     * Set prenom
     *
     * @param string $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     * Get nbVisites
     *
     * @return integer $nbVisites
     */
    public function getNbVisites()
    {
        return $this->nbVisites;
    }
    
    /**
     * Add nbVisites
     *
     * @param integer $nbVisites
     */
    public function addNbVisites()
    {
        $this->nbVisites++;
    }

    /**
     * Get region
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $region
     */
    public function getRegion()
    {
        return $this->region;
    }
    
    /**
     * Set region
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $region
     */
    public function setRegion($region)
    {
        if($region instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->region = $region;
        else
            $this->region = null;
    }

    /*--- Gestion domaine ---*/

    /**
     * Get domaine
     *
     * @return String $domaine
     */
    public function getDomainesString()
    {
        $domaineString = '';

        if(is_null($this->domaines))
        {
            return $domaineString;
        }

        foreach ($this->domaines as $domaine) 
        {
            $domaineString .= ($domaineString != '' ? ' | ' : ' ') . $domaine->getNom();
        }

        return $domaineString;
    }

    /**
     * Get les ids des domaines concerné par l'user
     *
     * @return array[integer]
     */
    public function getDomainesId()
    {
        $domainesId = array();

        if(is_null($this->domaines))
        {
            return $domainesId;
        }

        foreach ($this->domaines as $domaine) 
        {
            $domainesId[] = $domaine->getId();
        }

        return $domainesId;
    }

    /**
     * Add domaine
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     * @return Resultat
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        $this->domaines[] = $domaine;
    
        return $this;
    }

    /**
     * Remove domaine
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        $this->domaines->removeElement($domaine);
    }

    /**
     * Set domaines
     *
     * @param \Doctrine\Common\Collections\Collection $domaines
     * @return Domaine
     */
    public function setDomaines($domaines)
    {        
        $this->domaines = $domaines;
    
        return $this;
    }

    /**
     * Get domaines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /*-- Fin gestion domaine --*/
    
    /**
     * Get département
     *
     * @return \HopitalNumerique\DomaineBundle\Entity\Domaine $departement
     */
    public function getDepartement()
    {
        return $this->departement;
    }
    
    /**
     * Set département
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $departement
     */
    public function setDepartement($departement)
    {
        if($departement instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->departement = $departement;
        else
            $this->departement = null;
    }
    
    /**
     * Get ville
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $departement
     */
    public function getVille()
    {
        return $this->ville;
    }
    
    /**
     * Set ville
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $ville
     */
    public function setVille($ville)
    {
        if($ville instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference ){
            $this->ville = $ville;
        } else {
            $this->ville = null;
        }
    }
    
    /**
     * Get etat
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $etat
     */
    public function getEtat()
    {
        return $this->etat;
    }
    
    /**
     * Set etat
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $etat
     */
    public function setEtat($etat)
    {
        if($etat instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->etat = $etat;
        else
            $this->etat = null;
    }
    
    /**
     * Get titre
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $titre
     */
    public function getTitre()
    {
        return $this->titre;
    }
    
    /**
     * Set titre
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $titre
     */
    public function setTitre($titre)
    {
        if($titre instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->titre = $titre;
        else
            $this->titre = null;
    }
    
    /**
     * Get civilite
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $civilite
     */
    public function getCivilite()
    {
        return $this->civilite;
    }
    
    /**
     * Set civilite
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $civilite
     */
    public function setCivilite($civilite)
    {
        if($civilite instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->civilite = $civilite;
        else
            $this->civilite = null;
    }
    
    /**
     * Get telephoneDirect
     *
     * @return string $telephoneDirect
     */
    public function getTelephoneDirect()
    {
        return $this->telephoneDirect;
    }
    
    /**
     * Set telephoneDirect
     *
     * @param string $telephoneDirect
     */
    public function setTelephoneDirect($telephoneDirect)
    {
        $this->telephoneDirect = $telephoneDirect;
    }
    
    /**
     * Get telephonePortable
     *
     * @return string $telephonePortable
     */
    public function getTelephonePortable()
    {
        return $this->telephonePortable;
    }
    
    /**
     * Set telephonePortable
     *
     * @param string $telephonePortable
     */
    public function setTelephonePortable($telephonePortable)
    {
        $this->telephonePortable = $telephonePortable;
    }
    
    /**
     * Get contactAutre
     *
     * @return string $contactAutre
     */
    public function getContactAutre()
    {
        return $this->contactAutre;
    }
    
    /**
     * Set contactAutre
     *
     * @param string $contactAutre
     */
    public function setContactAutre($contactAutre)
    {
        $this->contactAutre = $contactAutre;
    }
    
    /**
     * Set typeActivite
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $typeActivite
     */
    public function setTypeActivite($typeActivite = null)
    {
        if($typeActivite instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->typeActivite = $typeActivite;
        else
            $this->typeActivite = null;
    }
    
    /**
     * Get typeActivite
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $typeActivite
     */
    public function getTypeActivite()
    {
        return $this->typeActivite;
    }
    
    /**
     * Set statutEtablissementSante
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $statutEtablissementSante
     */
    public function setStatutEtablissementSante($statutEtablissementSante)
    {
        if($statutEtablissementSante instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->statutEtablissementSante = $statutEtablissementSante;
        else
            $this->statutEtablissementSante = null;
    }
    
    /**
     * Get statutEtablissementSante
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $statutEtablissementSante
     */
    public function getStatutEtablissementSante()
    {
        return $this->statutEtablissementSante;
    }
    
    /**
     * Get etablissementRattachementSante
     *
     * @return string $etablissementRattachementSante
     */
    public function getEtablissementRattachementSante()
    {
        return $this->etablissementRattachementSante;
    }
    
    /**
     * Set etablissementRattachementSante
     *
     * @param string $etablissementRattachementSante
     */
    public function setEtablissementRattachementSante($etablissementRattachementSante)
    {
        if($etablissementRattachementSante instanceof \HopitalNumerique\EtablissementBundle\Entity\Etablissement )
            $this->etablissementRattachementSante = $etablissementRattachementSante;
        else
            $this->etablissementRattachementSante = null;
    }
    
    /**
     * Get autreStructureRattachementSante
     *
     * @return string $autreStructureRattachementSante
     */
    public function getAutreStructureRattachementSante()
    {
        return $this->autreStructureRattachementSante;
    }
    
    /**
     * Set autreStructureRattachementSante
     *
     * @param string $autreStructureRattachementSante
     */
    public function setAutreStructureRattachementSante($autreStructureRattachementSante)
    {
        $this->autreStructureRattachementSante = $autreStructureRattachementSante;
    }
    
    /**
     * Get fonctionDansEtablissementSante
     *
     * @return string $fonctionDansEtablissementSante
     */
    public function getFonctionDansEtablissementSante()
    {
        return $this->fonctionDansEtablissementSante;
    }
    
    /**
     * Set fonctionDansEtablissementSante
     *
     * @param string $fonctionDansEtablissementSante
     */
    public function setFonctionDansEtablissementSante($fonctionDansEtablissementSante)
    {
        $this->fonctionDansEtablissementSante = $fonctionDansEtablissementSante;
    }
    
    /**
     * Set fonctionDansEtablissementSanteReferencement
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $fonctionDansEtablissementSanteReferencement
     */
    public function setFonctionDansEtablissementSanteReferencement($fonctionDansEtablissementSanteReferencement)
    {
        if($fonctionDansEtablissementSanteReferencement instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->fonctionDansEtablissementSanteReferencement = $fonctionDansEtablissementSanteReferencement;
        else
            $this->fonctionDansEtablissementSanteReferencement = null;
    }
    
    /**
     * Get fonctionDansEtablissementSanteReferencement
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $fonctionDansEtablissementSanteReferencement
     */
    public function getFonctionDansEtablissementSanteReferencement()
    {
        return $this->fonctionDansEtablissementSanteReferencement;
    }
    
    /**
     * Set profilEtablissementSante
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $profilEtablissementSante
     */
    public function setProfilEtablissementSante($profilEtablissementSante)
    {
        if($profilEtablissementSante instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->profilEtablissementSante = $profilEtablissementSante;
        else
            $this->profilEtablissementSante = null;
    }
    
    /**
     * Get profilEtablissementSante
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $profilEtablissementSante
     */
    public function getProfilEtablissementSante()
    {
        return $this->profilEtablissementSante;
    }

    /**
     * Get nomStructure
     *
     * @return string $nomStructure
     */
    public function getNomStructure()
    {
        return $this->nomStructure;
    }
    
    /**
     * Set nomStructure
     *
     * @param string $nomStructure
     */
    public function setNomStructure($nomStructure)
    {
        $this->nomStructure = $nomStructure;
    }
    
    /**
     * Get fonctionStructure
     *
     * @return string $fonctionStructure
     */
    public function getFonctionStructure()
    {
        return $this->fonctionStructure;
    }
    
    /**
     * Set fonctionStructure
     *
     * @param string $fonctionStructure
     */
    public function setFonctionStructure($fonctionStructure)
    {
        $this->fonctionStructure = $fonctionStructure;
    }
    
    /**
     * Get lock
     *
     * @return boolean $lock
     */
    public function getLock()
    {
        return $this->lock;
    }
    
    /**
     * Set lock
     *
     * @param boolean $lock
     */
    public function setLock($lock)
    {
        $this->lock = $lock;
    }
    
    /**
     * Get notficationRequete
     *
     * @return boolean $notficationRequete
     */
    public function getNotficationRequete()
    {
        return $this->notficationRequete;
    }
    
    /**
     * Set notficationRequete
     *
     * @param boolean $notficationRequete
     */
    public function setNotficationRequete($notficationRequete)
    {
        $this->notficationRequete = $notficationRequete;
    }

    /**
     * Get archiver
     *
     * @return boolean $archiver
     */
    public function getArchiver()
    {
        return $this->archiver;
    }
    
    /**
     * Set archiver
     *
     * @param boolean $archiver
     */
    public function setArchiver($archiver)
    {
        $this->archiver = $archiver;
    }

    /**
     * Get alreadyBeAmbassadeur
     *
     * @return boolean $alreadyBeAmbassadeur
     */
    public function getAlreadyBeAmbassadeur()
    {
        return $this->alreadyBeAmbassadeur;
    }
    
    /**
     * Set alreadyBeAmbassadeur
     *
     * @param boolean $alreadyBeAmbassadeur
     */
    public function setAlreadyBeAmbassadeur($alreadyBeAmbassadeur)
    {
        $this->alreadyBeAmbassadeur = $alreadyBeAmbassadeur;
    }

    /**
     * Get alreadyBeExpert
     *
     * @return boolean $alreadyBeExpert
     */
    public function getAlreadyBeExpert()
    {
        return $this->alreadyBeExpert;
    }
    
    /**
     * Set alreadyBeExpert
     *
     * @param boolean $alreadyBeExpert
     */
    public function setAlreadyBeExpert($alreadyBeExpert)
    {
        $this->alreadyBeExpert = $alreadyBeExpert;
    }

    /**
     * Add Contractualisation
     *
     * @param \HopitalNumerique\UserBundle\Entity\Contractualisation $contractualisations
     * @return User
     */
    public function addContractualisation(\HopitalNumerique\UserBundle\Entity\Contractualisation $contractualisations)
    {
        $this->contractualisations[] = $contractualisations;
    
        return $this;
    }
    
    /**
     * Remove Contractualisation
     *
     * @param \HopitalNumerique\UserBundle\Entity\Contractualisation $contractualisations
     */
    public function removeContractualisation(\HopitalNumerique\UserBundle\Entity\Contractualisation $contractualisations)
    {
        $this->contractualisations->removeElement($contractualisations);
    }
    
    /**
     * Get contractualisations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContractualisations()
    {
        return $this->contractualisations;
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
    
    // ^ -------- Gestion questionnaire  -------- ^
    
    /**
     * Add reponses
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses
     * @return User
     */
    public function addReponse(\HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses)
    {
        $this->reponses[] = $reponses;
    
        return $this;
    }
    
    /**
     * Remove reponses
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses
     */
    public function removeReponse(\HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses)
    {
        $this->reponses->removeElement($reponses);
    }
    
    /**
     * Get reponses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReponses()
    {
        return $this->reponses;
    }

    /**
     * Add questionnaireOccurrences
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Occurrence $questionnaireOccurrences
     * @return User
     */
    public function addQuestionnaireOccurrence(\HopitalNumerique\QuestionnaireBundle\Entity\Occurrence $questionnaireOccurrences)
    {
        $this->questionnaireOccurrences[] = $questionnaireOccurrences;

        return $this;
    }

    /**
     * Remove questionnaireOccurrences
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Occurrence $questionnaireOccurrences
     */
    public function removeQuestionnaireOccurrence(\HopitalNumerique\QuestionnaireBundle\Entity\Occurrence $questionnaireOccurrences)
    {
        $this->questionnaireOccurrences->removeElement($questionnaireOccurrences);
    }

    /**
     * Get questionnaireOccurrences
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestionnaireOccurrences()
    {
        return $this->questionnaireOccurrences;
    }
    
    // v -------- Gestion questionnaire  -------- v
    
    public function getTermsAccepted()
    {
        return $this->termsAccepted;
    }
    
    public function setTermsAccepted($termsAccepted)
    {
        $this->termsAccepted = (Boolean) $termsAccepted;
    }
    
    /**
     * Retourne le prénom puis le nom
     *
     * @return string
     */
    public function getPrenomNom()
    {
        return ucfirst($this->prenom) . ' ' . ucfirst($this->nom);
    }

    /**
     * Retourne le nom puis le prénom
     *
     * @return string
     */
    public function getNomPrenom()
    {
        return ucfirst($this->nom) . ' ' . ucfirst($this->prenom);
    }

    /**
     * Retourne si l'utilisateur a le rôle CMSI ou pas.
     *
     * @return boolean VRAI ssi l'utilisateur a le rôle CMSI
     */
    public function hasRoleCmsi()
    {
        return $this->hasRole(Role::$ROLE_CMSI_LABEL);
    }
    /**
     * Retourne si l'utilisateur a le rôle ES - Direction générale ou pas.
     *
     * @return boolean VRAI ssi l'utilisateur a le rôle ES - Direction générale
     */
    public function hasRoleDirecteur()
    {
        return $this->hasRole(Role::$ROLE_DIRECTEUR_LABEL);
    }

    /**
     * Retourne si l'utilisateur a le rôle ambassadeur ou pas.
     *
     * @return boolean VRAI ssi l'utilisateur a le rôle ambassadeur
     */
    public function hasRoleAmbassadeur()
    {
        return $this->hasRole(Role::$ROLE_AMBASSADEUR_LABEL);
    }

    /**
     * Retourne si l'utilisateur a le rôle ES ou pas.
     *
     * @return boolean VRAI ssi l'utilisateur a le rôle es
     */
    public function hasRoleEs()
    {
        return $this->hasRole(Role::$ROLE_ES_LABEL);
    }

    /**
     * Retourne si l'utilisateur a le rôle Expert ou pas.
     *
     * @return boolean VRAI ssi l'utilisateur a le rôle expert
     */
    public function hasRoleExpert()
    {
        return $this->hasRole(Role::$ROLE_EXPERT_LABEL);
    }

    /**
     * Add ConnaissancesAmbassadeur
     *
     * @param \HopitalNumerique\UserBundle\Entity\ConnaissanceAmbassadeur $contractualisations
     * @return User
     */
    public function addConnaissancesAmbassadeur(\HopitalNumerique\UserBundle\Entity\ConnaissanceAmbassadeur $connaissanceAmbassadeur)
    {
        $this->connaissancesAmbassadeurs[] = $connaissanceAmbassadeur;
    
        return $this;
    }
    
    /**
     * Remove ConnaissancesAmbassadeur
     *
     * @param \HopitalNumerique\UserBundle\Entity\ConnaissanceAmbassadeur $connaissanceAmbassadeur
     */
    public function removeConnaissancesAmbassadeur(\HopitalNumerique\UserBundle\Entity\ConnaissanceAmbassadeur $connaissanceAmbassadeur)
    {
        $this->connaissancesAmbassadeurs->removeElement($connaissanceAmbassadeur);
    }
    
    /**
     * Get connaissanceAmbassadeur
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConnaissancesAmbassadeurs()
    {
        return $this->connaissancesAmbassadeurs;
    }

    /**
     * Get connaissancesAmbassadeursSI
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConnaissancesAmbassadeursSI()
    {
        return $this->connaissancesAmbassadeursSI;
    }

    /**
     * Retourne les prénom et nom de l'utilisateur avec sa civilité.
     * 
     * @return string Appelation de l'utilisateur
     */
    public function getAppellation()
    {
        // ----Traitement pour transformer le prénom "Jean-luc robert" en "Jean-Luc Robert"
        //Récupération du prénom
        $prenom = strtolower($this->getPrenom());
        //Découpage du prénom sur le tiret
        $tempsPrenom = explode('-', $prenom);
        //Unsset de la variable
        $prenom = "";
        //Pour chaque bout on met une MAJ sur la première lettre de chaque mot, si il y en plusieurs c'est qu'il y avait un -
        foreach ($tempsPrenom as $key => $tempPrenom)
        {
            $prenom .= ("" !== $prenom) ? ('-' . ucwords($tempPrenom)) : ucwords($tempPrenom);
        }
        
        // ----Mise en majuscule du nom
        $nom = strtoupper($this->getNom());
        
        return ($this->civilite != null ? $this->civilite->getLibelle().' ' : '').$prenom.' '.$nom;
    }

    /**
     * Retourne si l'utilisateur est actif.
     * 
     * @return boolean VRAI ssi l'utilisateur est actif.
     */
    public function isActif()
    {
        return ($this->etat != null && $this->etat->getId() == self::$ETAT_ACTIF_ID);
    }
	
	/**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->roles[0];
    }

    public function getRoles()
    {
        $roles = parent::getRoles();

        if(in_array('ROLE_ADMINISTRATEUR_1', $roles))
        {
            $roles[] = 'ROLE_ADMIN';
            $roles[] = 'ROLE_SUPER_ADMIN';
            $roles[] = 'ROLE_ALLOWED_TO_SWITCH';
        }

        return $roles;
    }

    /**
     * Get raisonDesinscription
     *
     * @return string $raisonDesinscription
     */
    public function getRaisonDesinscription()
    {
        return $this->raisonDesinscription;
    }
    
    /**
     * Set raisonDesinscription
     *
     * @param string $raisonDesinscription
     */
    public function setRaisonDesinscription($raisonDesinscription)
    {
        $this->raisonDesinscription = $raisonDesinscription;
    }

    /**
     * Get remarque
     *
     * @return string $remarque
     */
    public function getRemarque()
    {
        return $this->remarque;
    }
    
    /**
     * Set remarque
     *
     * @param string $remarque
     */
    public function setRemarque($remarque)
    {
        $this->remarque = $remarque;
        return $this;
    }

    /**
     * Get biographie
     *
     * @return string $biographie
     */
    public function getBiographie()
    {
        return $this->biographie;
    }
    
    /**
     * Set biographie
     *
     * @param string $biographie
     */
    public function setBiographie($biographie)
    {
        $this->biographie = $biographie;
        return $this;
    }
    
    /**
     * Get dateLastUpdate
     *
     * @return DateTime $dateLastUpdate
     */
    public function getDateLastUpdate()
    {
        return $this->dateLastUpdate;
    }
    
    /**
     * Set dateLastUpdate
     *
     * @param DateTime $dateLastUpdate
     */
    public function setDateLastUpdate($dateLastUpdate)
    {
        $this->dateLastUpdate = $dateLastUpdate;
        return $this;
    }

    /**
     * Get dashboardFront
     *
     * @return string $dashboardFront
     */
    public function getDashboardFront()
    {
        return $this->dashboardFront;
    }
    
    /**
     * Set dashboardFront
     *
     * @param string $dashboardFront
     */
    public function setDashboardFront($dashboardFront)
    {
        $this->dashboardFront = $dashboardFront;
        return $this;
    }
    
    /**
     * Get dashboardBack
     *
     * @return string $dashboardBack
     */
    public function getDashboardBack()
    {
        return $this->dashboardBack;
    }
    
    /**
     * Set dashboardBack
     *
     * @param string $dashboardBack
     */
    public function setDashboardBack($dashboardBack)
    {
        $this->dashboardBack = $dashboardBack;
        return $this;
    }

    public function isGranted($role)
    {
        return in_array($role, $this->getRoles());
    }

    // ----------------------------------------
    // --- Gestion de l'upload des fichiers ---
    // ----------------------------------------
    
    /**
     * Set path
     *
     * @param string $path
     * @return Contractualisation
     */
    public function setPath($path)
    {
        if( is_null($path) && file_exists($this->getAbsolutePath()) )
            unlink($this->getAbsolutePath());
    
        $this->path = $path;
    
        return $this;
    }
    
    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }
    
    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }
    
    public function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __WEB_DIRECTORY__.'/'.$this->getUploadDir();
    }
    
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
        if (null !== $this->file){
            //delete Old File
            if ( file_exists($this->getAbsolutePath()) )
                unlink($this->getAbsolutePath());

            $tool = new Chaine( $this->getPrenomNom() );
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
        if (null === $this->file)
            return;
    
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
    
        if (file_exists($file) )
            unlink($file);
    }

    /**
     * Get Last ip connection
     * @return string
     */
    public function getIpLastConnection() {
      return $this->ipLastConnection;
    }

    /**
     * Set Last ip Connection
     * @param string $ip
     * @return string
     */
    public function setIpLastConnection($ip) {
      $this->ipLastConnection = $ip;
      return $this;
    }

    /* <-- Communauté de pratiques */

    /**
     * Get inscritCommunautePratique
     *
     * @return boolean $inscritCommunautePratique
     */
    public function isInscritCommunautePratique()
    {
        return $this->inscritCommunautePratique;
    }

    /**
     * Set inscritCommunautePratique
     *
     * @param boolean $inscritCommunautePratique
     */
    public function setInscritCommunautePratique($inscritCommunautePratique)
    {
        $this->inscritCommunautePratique = $inscritCommunautePratique;
    }
    
    /**
     * Add communautePratiqueGroupe
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupe
     * @return User
     */
    public function addCommunautePratiqueAnimateurGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupe)
    {
        $communautePratiqueGroupe->addAnimateur($this);
        $this->communautePratiqueAnimateurGroupes[] = $communautePratiqueGroupe;

        return $this;
    }

    /**
     * Remove communautePratiqueGroupes
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes
     */
    public function removeCommunautePratiqueAnimateurGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes)
    {
        $this->communautePratiqueAnimateurGroupes->removeElement($communautePratiqueGroupes);
    }

    /**
     * Get communautePratiqueGroupes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommunautePratiqueAnimateurGroupes()
    {
        return $this->communautePratiqueAnimateurGroupes;
    }

    /**
     * Add communautePratiqueGroupe
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupe
     * @return User
     */
    public function addCommunautePratiqueGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupe)
    {
        $communautePratiqueGroupe->addUser($this);
        $this->communautePratiqueGroupes[] = $communautePratiqueGroupe;

        return $this;
    }

    /**
     * Remove communautePratiqueGroupes
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes
     */
    public function removeCommunautePratiqueGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupes)
    {
        $this->communautePratiqueGroupes->removeElement($communautePratiqueGroupes);
    }

    /**
     * Has communautePratiqueGroupe ?
     * 
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupe
     * @return boolean
     */
    public function hasCommunautePratiqueGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupe)
    {
        foreach ($this->communautePratiqueGroupes as $communautePratiqueGroupeExistant)
        {
            if ($communautePratiqueGroupeExistant->getId() == $communautePratiqueGroupe->getId())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Get communautePratiqueGroupes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommunautePratiqueGroupes()
    {
        return $this->communautePratiqueGroupes;
    }

    /**
     * Add communautePratiqueDocument
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Document $communautePratiqueDocument
     * @return User
     */
    public function addCommunautePratiqueDocument(\HopitalNumerique\CommunautePratiqueBundle\Entity\Document $communautePratiqueDocument)
    {
        $this->communautePratiqueDocuments[] = $communautePratiqueDocument;

        return $this;
    }

    /**
     * Remove communautePratiqueDocuments
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Document $communautePratiqueDocument
     */
    public function removeCommunautePratiqueDocument(\HopitalNumerique\CommunautePratiqueBundle\Entity\Document $communautePratiqueDocument)
    {
        $this->communautePratiqueDocuments->removeElement($communautePratiqueDocument);
    }

    /**
     * Get communautePratiqueDocuments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommunautePratiqueDocuments()
    {
        return $this->communautePratiqueDocuments;
    }

    /* --> */

    /* <-- Avatar */

    /**
    * Retourne l'image de l'avatar à afficher (image générique si aucun avatar).
    *
    * @return string Avatar
    */
    public function getAvatarWebPath()
    {
        if (null !== $this->path)
        {
            return '/'.$this->getWebPath();
        }
        
        if (null !== $this->civilite && Reference::CIVILITE_MADAME_ID == $this->civilite->getId())
        {
            return '/bundles/hopitalnumeriqueuser/img/madame.png';
        }

        return '/bundles/hopitalnumeriqueuser/img/monsieur.png';
    }

    /* --> */
}
