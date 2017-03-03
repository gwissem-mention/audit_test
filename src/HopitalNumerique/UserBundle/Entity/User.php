<?php

namespace HopitalNumerique\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\QuestionnaireBundle\Entity\Occurrence;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use Nodevo\RoleBundle\Entity\Role;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Inscription;
//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use APY\DataGridBundle\Grid\Mapping as GRID;
use Gedmo\Mapping\Annotation as Gedmo;
//Tools
use Nodevo\ToolsBundle\Tools\Chaine;

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
     * @Assert\NotBlank()
     * @Assert\Length(
     *      max = "50",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @Nodevo\Javascript(class="validate[required,maxSize[50]]")
     * @ORM\Column(name="usr_nom", type="string", length=50, options = {"comment" = "Nom de l utilisateur"})
     * @Gedmo\Versioned
     */
    protected $nom;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *      max = "50",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le prénom."
     * )
     * @Nodevo\Javascript(class="validate[required,maxSize[50]]")
     * @ORM\Column(name="usr_prenom", type="string", length=50, options = {"comment" = "Prénom de l utilisateur"})
     * @Gedmo\Versioned
     */
    protected $prenom;

    /**
     * @var int
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
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_user_region",
     *      joinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")}
     * )
     */
    private $rattachementRegions;

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
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le Nom de votre structure si non disponible dans la liste précédente.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le Nom de votre structure si non disponible dans la liste précédente."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @Gedmo\Versioned
     * @ORM\Column(name="usr_autre_rattachement_sante", type="string", length=255, nullable=true, options = {"comment" = "Nom de votre structure si non disponible dans la liste précédente santé de l utilisateur"})
     */
    protected $autreStructureRattachementSante;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = "3",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le Nom de votre structure si non disponible dans la liste précédente.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le Nom de votre structure si non disponible dans la liste précédente."
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

    // v -------- Onglet : Vous êtes dans une autre structure  -------- v

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
     * @ORM\Column(name="usr_lock", type="boolean", options = {"comment" = "L utilisateur est-il verrouillé ?"})
     */
    protected $lock;

    /**
     * @var bool
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
     * @var ArrayCollection
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
     *     maxSize = "1000k",
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
     * @var bool
     *
     * @ORM\Column(name="usr_already_be_ambassadeur", type="boolean", options = {"comment" = "A deja ete ambassadeur ?"})
     */
    protected $alreadyBeAmbassadeur;

    /**
     * @var bool
     *
     * @ORM\Column(name="usr_already_be_expert", type="boolean", options = {"comment" = "A deja ete expert ?"})
     */
    protected $alreadyBeExpert;

    /**
     * @var bool
     *
     * @ORM\Column(name="usr_notification_requete", type="boolean", options = {"comment" = "L utilisateur est notifie par mail des maj des publications ?"})
     */
    protected $notficationRequete;

    /* <-- Communauté de pratique */

    /**
     * @var bool
     *
     * @Assert\NotNull()
     * @ORM\Column(name="usr_inscrit_communaute_pratique", type="boolean", options={"default"=false,"comment"="Indique si l utilisateur est inscrit à la communauté de pratiques"})
     */
    private $inscritCommunautePratique;

    /**
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe", mappedBy="animateurs", cascade={"persist", "remove"})
     */
    private $communautePratiqueAnimateurGroupes;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Inscription", mappedBy="user", cascade={"persist", "remove"})
     */
    private $groupeInscription;

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
        $this->pseudonymeForum = '';
        $this->enabled = 1;
        $this->civilite = [];
        $this->lock = false;
        $this->archiver = false;
        $this->alreadyBeAmbassadeur = false;
        $this->alreadyBeExpert = false;
        $this->nbVisites = 0;
        $this->notficationRequete = true;
        $this->inscritCommunautePratique = false;
        $this->previousAdmin = false;
        $this->typeActivite = new ArrayCollection();
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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateInscription.
     *
     * @param \DateTime $dateInscription
     *
     * @return User
     */
    public function setDateInscription($dateInscription)
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    /**
     * Get dateInscription.
     *
     * @return \DateTime
     */
    public function getDateInscription()
    {
        return $this->dateInscription;
    }

    /**
     * Get dateInscription string.
     *
     * @return string
     */
    public function getDateInscriptionString()
    {
        return $this->dateInscription->format('d/m/Y');
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
     * Get pseudonymeForum.
     *
     * @return string $pseudonymeForum
     */
    public function getPseudonymeForum()
    {
        return $this->pseudonymeForum;
    }

    /**
     * Set pseudonymeForum.
     *
     * @param string $pseudonymeForum
     */
    public function setPseudonymeForum($pseudonymeForum)
    {
        $this->pseudonymeForum = $pseudonymeForum;
    }

    /**
     * Get nom.
     *
     * @return string $nom
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set nom.
     *
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * Get prenom.
     *
     * @return string $prenom
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set prenom.
     *
     * @param string $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     * Set email.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;

        $this->setUsername($email);
    }

    /**
     * Get nbVisites.
     *
     * @return int $nbVisites
     */
    public function getNbVisites()
    {
        return $this->nbVisites;
    }

    /**
     * Add nbVisites.
     *
     * @param int $nbVisites
     */
    public function addNbVisites()
    {
        ++$this->nbVisites;
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
     */
    public function setRegion($region)
    {
        if ($region instanceof Reference) {
            $this->region = $region;
        } else {
            $this->region = null;
        }
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
     */
    public function removeRattachementRegion(Reference $rattachementRegions)
    {
        $this->rattachementRegions->removeElement($rattachementRegions);
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

        if (is_null($this->domaines)) {
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

        if (is_null($this->domaines)) {
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
     * @return Domaine
     */
    public function setDomaines($domaines)
    {
        $this->domaines = $domaines;

        return $this;
    }

    /**
     * Get domaines.
     *
     * @return Collection
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
     * Get département.
     *
     * @return Domaine $departement
     */
    public function getDepartement()
    {
        return $this->departement;
    }

    /**
     * Set département.
     *
     * @param Reference $departement
     */
    public function setDepartement($departement)
    {
        if ($departement instanceof Reference) {
            $this->departement = $departement;
        } else {
            $this->departement = null;
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
     */
    public function setEtat($etat)
    {
        if ($etat instanceof Reference) {
            $this->etat = $etat;
        } else {
            $this->etat = null;
        }
    }

    /**
     * Get titre.
     *
     * @return Reference $titre
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set titre.
     *
     * @param Reference $titre
     */
    public function setTitre($titre)
    {
        if ($titre instanceof Reference) {
            $this->titre = $titre;
        } else {
            $this->titre = null;
        }
    }

    /**
     * Get civilite.
     *
     * @return Reference $civilite
     */
    public function getCivilite()
    {
        return $this->civilite;
    }

    /**
     * Set civilite.
     *
     * @param Reference $civilite
     */
    public function setCivilite($civilite)
    {
        if ($civilite instanceof Reference) {
            $this->civilite = $civilite;
        } else {
            $this->civilite = null;
        }
    }

    /**
     * Get telephoneDirect.
     *
     * @return string $telephoneDirect
     */
    public function getTelephoneDirect()
    {
        return $this->telephoneDirect;
    }

    /**
     * Set telephoneDirect.
     *
     * @param string $telephoneDirect
     */
    public function setTelephoneDirect($telephoneDirect)
    {
        $this->telephoneDirect = $telephoneDirect;
    }

    /**
     * Get telephonePortable.
     *
     * @return string $telephonePortable
     */
    public function getTelephonePortable()
    {
        return $this->telephonePortable;
    }

    /**
     * Set telephonePortable.
     *
     * @param string $telephonePortable
     */
    public function setTelephonePortable($telephonePortable)
    {
        $this->telephonePortable = $telephonePortable;
    }

    /**
     * Get contactAutre.
     *
     * @return string $contactAutre
     */
    public function getContactAutre()
    {
        return $this->contactAutre;
    }

    /**
     * Set contactAutre.
     *
     * @param string $contactAutre
     */
    public function setContactAutre($contactAutre)
    {
        $this->contactAutre = $contactAutre;
    }

    /**
     * Set typeActivite.
     *
     * @param array <\HopitalNumerique\ReferenceBundle\Entity\Reference> $activiteTypes
     *
     * @return $this
     */
    public function setTypeActivites($activiteTypes)
    {
        $this->typeActivite = new ArrayCollection();

        foreach ($activiteTypes as $activiteType) {
            $this->addTypeActivite($activiteType);
        }

        return $this;
    }

    /**
     * Set typeActivite.
     *
     * @param Reference $typeActivite
     */
    public function setTypeActivite($typeActivite = null)
    {
        if ($typeActivite instanceof Reference) {
            $this->typeActivite = $typeActivite;
        } else {
            $this->typeActivite = null;
        }
    }

    /**
     * Add typeActivite.
     *
     * @param Reference $typeActivite
     *
     * @return User
     */
    public function addTypeActivite(Reference $typeActivite)
    {
        $this->typeActivite[] = $typeActivite;

        return $this;
    }

    /**
     * Get typeActivite.
     *
     * @return Reference $typeActivite
     */
    public function getTypeActivite()
    {
        return $this->typeActivite;
    }

    /**
     * Retourne si l'utilisateur possède tel type d'activité.
     *
     * @param Reference $activiteType Type d'activité
     *
     * @return bool Si possède
     */
    public function hasTypeActivite(Reference $activiteType)
    {
        foreach ($this->typeActivite as $existingActiviteType) {
            if ($activiteType->equals($existingActiviteType)) {
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
    public function equalsTypeActivite(array $activiteTypes)
    {
        if (count($this->typeActivite) === count($activiteTypes)) {
            foreach ($activiteTypes as $activiteType) {
                if (!$this->hasTypeActivite($activiteType)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Set statutEtablissementSante.
     *
     * @param Reference $statutEtablissementSante
     */
    public function setStatutEtablissementSante($statutEtablissementSante)
    {
        if ($statutEtablissementSante instanceof Reference) {
            $this->statutEtablissementSante = $statutEtablissementSante;
        } else {
            $this->statutEtablissementSante = null;
        }
    }

    /**
     * Get statutEtablissementSante.
     *
     * @return Reference $statutEtablissementSante
     */
    public function getStatutEtablissementSante()
    {
        return $this->statutEtablissementSante;
    }

    /**
     * Get etablissementRattachementSante.
     *
     * @return string $etablissementRattachementSante
     */
    public function getEtablissementRattachementSante()
    {
        return $this->etablissementRattachementSante;
    }

    /**
     * Get etablissementRattachementSanteString.
     *
     * @return string $etablissementRattachementSante
     */
    public function getEtablissementRattachementSanteString()
    {
        if (is_object($this->etablissementRattachementSante)) {
            return $this->etablissementRattachementSante->getNom();
        }
    }

    /**
     * Set etablissementRattachementSante.
     *
     * @param string $etablissementRattachementSante
     */
    public function setEtablissementRattachementSante($etablissementRattachementSante)
    {
        if ($etablissementRattachementSante instanceof Etablissement) {
            $this->etablissementRattachementSante = $etablissementRattachementSante;
        } else {
            $this->etablissementRattachementSante = null;
        }
    }

    /**
     * Get autreStructureRattachementSante.
     *
     * @return string $autreStructureRattachementSante
     */
    public function getAutreStructureRattachementSante()
    {
        return $this->autreStructureRattachementSante;
    }

    /**
     * Retourne le nom de l'établissement de l'utilisateur
     * (celui de la liste ou celui qu'il a saisi).
     *
     * @return string
     */
    public function getNomEtablissement()
    {
        if ($this->getEtablissementRattachementSanteString() != null) {
            return $this->getEtablissementRattachementSanteString();
        } elseif ($this->getAutreStructureRattachementSante() != null) {
            return $this->getAutreStructureRattachementSante();
        } else {
            return 'Aucune structure de rattachement';
        }
    }

    /**
     * Set autreStructureRattachementSante.
     *
     * @param string $autreStructureRattachementSante
     */
    public function setAutreStructureRattachementSante($autreStructureRattachementSante)
    {
        $this->autreStructureRattachementSante = $autreStructureRattachementSante;
    }

    /**
     * Get fonctionDansEtablissementSante.
     *
     * @return string $fonctionDansEtablissementSante
     */
    public function getFonctionDansEtablissementSante()
    {
        return $this->fonctionDansEtablissementSante;
    }

    /**
     * Set fonctionDansEtablissementSante.
     *
     * @param string $fonctionDansEtablissementSante
     */
    public function setFonctionDansEtablissementSante($fonctionDansEtablissementSante)
    {
        $this->fonctionDansEtablissementSante = $fonctionDansEtablissementSante;
    }

    /**
     * Set fonctionDansEtablissementSanteReferencement.
     *
     * @param Reference $fonctionDansEtablissementSanteReferencement
     */
    public function setFonctionDansEtablissementSanteReferencement($fonctionDansEtablissementSanteReferencement)
    {
        if ($fonctionDansEtablissementSanteReferencement instanceof Reference) {
            $this->fonctionDansEtablissementSanteReferencement = $fonctionDansEtablissementSanteReferencement;
        } else {
            $this->fonctionDansEtablissementSanteReferencement = null;
        }
    }

    /**
     * Get fonctionDansEtablissementSanteReferencement.
     *
     * @return Reference $fonctionDansEtablissementSanteReferencement
     */
    public function getFonctionDansEtablissementSanteReferencement()
    {
        return $this->fonctionDansEtablissementSanteReferencement;
    }

    /**
     * Set profilEtablissementSante.
     *
     * @param Reference $profilEtablissementSante
     */
    public function setProfilEtablissementSante($profilEtablissementSante)
    {
        if ($profilEtablissementSante instanceof Reference) {
            $this->profilEtablissementSante = $profilEtablissementSante;
        } else {
            $this->profilEtablissementSante = null;
        }
    }

    /**
     * Get profilEtablissementSante.
     *
     * @return Reference $profilEtablissementSante
     */
    public function getProfilEtablissementSante()
    {
        return $this->profilEtablissementSante;
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
     * @return string $fonctionStructure
     */
    public function getFonctionStructure()
    {
        return $this->fonctionStructure;
    }

    /**
     * Set fonctionStructure.
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

    public function getTermsAccepted()
    {
        return $this->termsAccepted;
    }

    public function setTermsAccepted($termsAccepted)
    {
        $this->termsAccepted = (bool) $termsAccepted;
    }

    /**
     * Retourne le prénom puis le nom.
     *
     * @return string
     */
    public function getPrenomNom()
    {
        return ucfirst($this->prenom) . ' ' . ucfirst($this->nom);
    }

    /**
     * Retourne le nom puis le prénom.
     *
     * @return string
     */
    public function getNomPrenom()
    {
        return ucfirst($this->nom) . ' ' . ucfirst($this->prenom);
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

    public function hasRoleAdminHn()
    {
        return $this->hasRole(Role::$ROLE_ADMIN_HN_LABEL);
    }

    public function hasRoleAdminDomaine()
    {
        return $this->hasRole(Role::$ROLE_ADMIN_DOMAINE);
    }

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
     * Add ConnaissancesAmbassadeur.
     *
     * @param ConnaissanceAmbassadeur $contractualisations
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
     * @return Collection
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

        if (is_null($this->connaissancesAmbassadeurs)) {
            return $ambassadeurString;
        }

        foreach ($this->connaissancesAmbassadeurs as $ambassadeur) {
            if ($ambassadeur->getDomaine()) {
                $ambassadeurString .= ($ambassadeurString != '' ? ' | ' : ' ') . $ambassadeur->getDomaine()->getLibelle();
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
        $prenom = '';
        //Pour chaque bout on met une MAJ sur la première lettre de chaque mot, si il y en plusieurs c'est qu'il y avait un -
        foreach ($tempsPrenom as $key => $tempPrenom) {
            $prenom .= ('' !== $prenom) ? ('-' . ucwords($tempPrenom)) : ucwords($tempPrenom);
        }

        // ----Mise en majuscule du nom
        $nom = strtoupper($this->getNom());

        return ($this->civilite != null ? $this->civilite->getLibelle() . ' ' : '') . $prenom . ' ' . $nom;
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
     * Get biographie.
     *
     * @return string $biographie
     */
    public function getBiographie()
    {
        return $this->biographie;
    }

    /**
     * Set biographie.
     *
     * @param string $biographie
     *
     * @return User
     */
    public function setBiographie($biographie)
    {
        $this->biographie = $biographie;

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
     * @param DateTime $dateLastUpdate
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

    public function isGranted($role)
    {
        return in_array($role, $this->getRoles());
    }

    // ----------------------------------------
    // --- Gestion de l'upload des fichiers ---
    // ----------------------------------------

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return User
     */
    public function setPath($path)
    {
        if (is_null($path) && file_exists($this->getAbsolutePath())) {
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

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    public function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __WEB_DIRECTORY__ . '/' . $this->getUploadDir();
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

    /* <-- Communauté de pratique */

    /**
     * Get inscritCommunautePratique.
     *
     * @return bool $inscritCommunautePratique
     */
    public function isInscritCommunautePratique()
    {
        return $this->inscritCommunautePratique;
    }

    /**
     * Set inscritCommunautePratique.
     *
     * @param bool $inscritCommunautePratique
     */
    public function setInscritCommunautePratique($inscritCommunautePratique)
    {
        $this->inscritCommunautePratique = $inscritCommunautePratique;
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
        $groupeInscription->getgroupe->addAnimateur($this);
        $this->communautePratiqueAnimateurGroupes[] = $groupeInscription->getGroupe();

        return $this;
    }

    /**
     * Remove communautePratiqueGroupes.
     *
     * @param Groupe $communautePratiqueGroupes
     */
    public function removeCommunautePratiqueAnimateurGroupe(Groupe $communautePratiqueGroupes)
    {
        $this->removeCommunautePratiqueAnimateurGroupeInscription(new Inscription($communautePratiqueGroupes, $this));
    }

    /**
     * Remove groupeInscription.
     *
     * @param Inscription $groupeInscription
     */
    public function removeCommunautePratiqueAnimateurGroupeInscription(Inscription $groupeInscription)
    {
        $this->communautePratiqueAnimateurGroupes->removeElement($groupeInscription->getGroupe());
        $groupeInscription->getGroupe()->removeAnimateur($this);
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
    public function addCommunautePratiqueGroupe(Groupe $communautePratiqueGroupe)
    {
        $this->addCommunautePratiqueGroupeInscription(new Inscription($communautePratiqueGroupe, $this));

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
     * @param Inscription $groupeInscription
     *
     * @return User
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
     * @return User
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
     * Has $groupeInscription ?
     *
     * @param Inscription $groupeInscription
     *
     * @return bool
     */
    public function hasCommunautePratiqueGroupeInscription(Inscription $groupeInscription)
    {
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
        foreach ($this->getGroupeInscription() as $inscrit) {
            $this->communautePratiqueGroupes[] = $inscrit->getGroupe();
        }

        return $this->communautePratiqueGroupes;
    }

    /**
     * Get groupeInscription.
     *
     * @return Collection
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

    /* --> */

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

    /* <-- Avatar */

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

        if (null !== $this->civilite && Reference::CIVILITE_MADAME_ID == $this->civilite->getId()) {
            return '/bundles/hopitalnumeriqueuser/img/madame.png';
        }

        return '/bundles/hopitalnumeriqueuser/img/monsieur.png';
    }

    /* --> */

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
}
