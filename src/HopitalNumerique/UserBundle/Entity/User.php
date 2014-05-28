<?php

namespace HopitalNumerique\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Nodevo\RoleBundle\Entity\Role;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * User
 *
 * @ORM\Table("core_user")
 * @ORM\Entity(repositoryClass="HopitalNumerique\UserBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Cette adresse email existe déjà.")
 * @UniqueEntity(fields="username", message="Ce nom de compte existe déjà.")
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
     */
    protected $username;
    
    /**
     * @var string
     * @Assert\NotBlank(message="L'adresse éléctronique ne peut pas être vide.")
     * @Assert\Regex(pattern= "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]{2,}[.][a-zA-Z]{2,3}$/")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom de compte.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom de compte."
     * )
     * @Nodevo\Javascript(class="validate[required,custom[email]]")
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
     *
     * @GRID\Column(field="region.libelle")
     */
    protected $region;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_departement", referencedColumnName="ref_id")
     */
    protected $departement;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="L'état ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $etat;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_titre", referencedColumnName="ref_id")
     */
    protected $titre;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_civilite", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="La civilité ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $civilite;
    
    /**
     * @var string
     * @Assert\Length(
     *      min = "14",
     *      max = "14",
     *      minMessage="Le numéro de téléphone direct doit être composé de {{ limit }} caractères.",
     *      maxMessage="Le numéro de téléphone direct doit être composé de {{ limit }} caractères."
     * )
     * @Nodevo\Javascript(class="validate[minSize[14],maxSize[14]],custom[phone]", mask="99 99 99 99 99")
     * @ORM\Column(name="usr_telephone_direct", type="string", length=14, nullable=true, options = {"comment" = "Téléphone de l utilisateur"})
     */
    protected $telephoneDirect;
    
    /**
     * @var string
     * @Assert\Length(
     *      min = "14",
     *      max = "14",
     *      minMessage="Le numéro de téléphone portable doit être composé de {{ limit }} caractères.",
     *      maxMessage="Le numéro de téléphone portable doit être composé de {{ limit }} caractères."
     * )
     * @Nodevo\Javascript(class="validate[minSize[14],maxSize[14]],custom[phone]", mask="99 99 99 99 99")
     * @ORM\Column(name="usr_telephone_portable", type="string", length=14, nullable=true, options = {"comment" = "Téléphone portable de l utilisateur"})
     */
    protected $telephonePortable;
    
    /**
     * @var string
     * @ORM\Column(name="usr_contact_autre", type="text", nullable=true, options = {"comment" = "Autre moyen de contacter l utilsateur"})
     */
    protected $contactAutre;
    
    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="core_user_domaines",
     *      joinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")}
     * )
     */
    protected $domaines;

    // ^ -------- Onglet : Vous êtes un établissement de santé -------- ^
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut_etablissement_sante", referencedColumnName="ref_id")
     */
    protected $statutEtablissementSante;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\EtablissementBundle\Entity\Etablissement", inversedBy="usersRattachement", cascade={"persist"})
     * @ORM\JoinColumn(name="eta_etablissement_rattachement_sante", referencedColumnName="eta_id")
     */
    protected $etablissementRattachementSante;
    
    /**
     * @var string
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans l'Nom de votre établissement si non disponible dans la liste précédente.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans l'Nom de votre établissement si non disponible dans la liste précédente."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="usr_autre_rattachement_sante", type="string", length=255, nullable=true, options = {"comment" = "Nom de votre établissement si non disponible dans la liste précédente santé de l utilisateur"})
     */
    protected $autreStructureRattachementSante;
    
    /**
     * @var string
     * @Assert\Length(
     *      min = "3",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans l'Nom de votre établissement si non disponible dans la liste précédente.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans l'Nom de votre établissement si non disponible dans la liste précédente."
     * )
     * @Nodevo\Javascript(class="validate[minSize[3],maxSize[255]]")
     * @ORM\Column(name="usr_fonction_dans_etablissement", type="string", length=255, nullable=true, options = {"comment" = "Fonction dans l etablissement de santé de l utilisateur"})
     */
    protected $fonctionDansEtablissementSante;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_profil_etablissement_sante", referencedColumnName="ref_id")
     */
    protected $profilEtablissementSante;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_raison_inscription_sante", referencedColumnName="ref_id")
     */
    protected $raisonInscriptionSante;

    // v -------- Onglet : Vous êtes un établissement de santé -------- v
    
    // ^ -------- Onglet : Vous êtes une structure autre qu'un établissement de santé  -------- ^

    /**
     * @var string
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom de la structure.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom de la structure."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="usr_nom_structure", type="string", length=255, nullable=true, options = {"comment" = "Nom de la structure de l utilisateur"})
     */
    protected $nomStructure;
    
    /**
     * @var string
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans la fonction de la structure.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans la fonction de la structure."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="usr_fonction_strucutre", type="string", length=255, nullable=true, options = {"comment" = "Fonction au sein de la structure"})
     */
    protected $fonctionStructure;
    

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_raison_inscription_structure", referencedColumnName="ref_id")
     */
    protected $raisonInscriptionStructure;
    
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
     * @ORM\Column(name="usr_lock", type="boolean", options = {"comment" = "L utilisateur est-il verrouillé ?"}) */
    protected $lock;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usr_archiver", type="boolean", options = {"comment" = "L utilisateur est-il archivé ?"}) */
    protected $archiver;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\QuestionnaireBundle\Entity\Reponse", mappedBy="user", cascade={"persist", "remove" })
     */
    protected $reponses;

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
     */
    protected $raisonDesinscription;
    
    
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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->objets               = new \Doctrine\Common\Collections\ArrayCollection();
        $this->username             = '';
        $this->enabled              = 1;
        $this->civilite             = array();
        $this->lock                 = false;
        $this->archiver             = false;
        $this->domaines             = new \Doctrine\Common\Collections\ArrayCollection();
        $this->nbVisites            = 0;    }

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
     * Set prenom
     *
     * @param string $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
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
    
    /**
     * Get département
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $departement
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
     * Set raisonInscriptionSante
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $raisonInscriptionSante
     */
    public function setRaisonInscriptionSante($raisonInscriptionSante)
    {
        if($raisonInscriptionSante instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->raisonInscriptionSante = $raisonInscriptionSante;
        else
            $this->raisonInscriptionSante = null;
    }
    
    /**
     * Get raisonInscriptionSante
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $raisonInscriptionSante
     */
    public function getRaisonInscriptionSante()
    {
        return $this->raisonInscriptionSante;
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
     * Set raisonInscriptionStructure
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $raisonInscriptionStructure
     */
    public function setRaisonInscriptionStructure($raisonInscriptionStructure)
    {
        if($raisonInscriptionStructure instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->raisonInscriptionStructure = $raisonInscriptionStructure;
        else
            $this->raisonInscriptionStructure = null;
    }
    
    /**
     * Get raisonInscriptionStructure
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $raisonInscriptionStructure
     */
    public function getRaisonInscriptionStructure()
    {
        return $this->raisonInscriptionStructure;
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
     * Add domaine
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $domaine
     * @return Objet
     */
    public function addDomaine(\HopitalNumerique\ReferenceBundle\Entity\Reference $domaine)
    {
        $this->domaines[] = $domaine;
    
        return $this;
    }

    /**
     * Remove domaine
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $domaine
     */
    public function removeDomaine(\HopitalNumerique\ReferenceBundle\Entity\Reference $domaine)
    {
        $this->domaines->removeElement($domaine);
    }

    /**
     * Set domaines
     *
     * @param \Doctrine\Common\Collections\Collection $domaines
     * @return Objet
     */
    public function setDomaines(array $domaines)
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
}