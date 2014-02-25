<?php

namespace HopitalNumerique\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nodevo\UserBundle\Entity\User as BaseUser;
use Nodevo\RoleBundle\Entity\Role as Role;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * User
 *
 * @ORM\Table("core_user")
 * @ORM\Entity(repositoryClass="HopitalNumerique\UserBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Cette adresse email existe déjà.")
 * @UniqueEntity(fields="username", message="Ce nom d'utilisateur existe déjà.")
 */
class User extends BaseUser
{
    /**
     * @ORM\Column(name="usr_id", type="integer", options = {"comment" = "ID de l utilisateur"})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Le nom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "3",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[3],maxSize[50]]")
     * @ORM\Column(name="usr_nom", type="string", length=50, options = {"comment" = "Nom de l utilisateur"})
     */
    protected $nom;   
    
    /**
     * @var string
     * @Assert\NotBlank(message="Le prénom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "3",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le prénom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le prénom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[3],maxSize[50]]")
     * @ORM\Column(name="usr_prenom", type="string", length=50, options = {"comment" = "Prénom de l utilisateur"})
     */
    protected $prenom;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_region", referencedColumnName="ref_id")
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
     * @ORM\ManyToMany(targetEntity="\Nodevo\RoleBundle\Entity\Role", inversedBy="users")
     * @ORM\JoinTable(name="core_user_role",
     *      joinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ro_id", referencedColumnName="ro_id")}
     * )
     */
    protected $roles;
    
    
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
     *      min = "3",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans l'autre structure de rattachement.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans l'autre structure de rattachement."
     * )
     * @Nodevo\Javascript(class="validate[minSize[3],maxSize[255]]")
     * @ORM\Column(name="usr_autre_rattachement_sante", type="string", length=255, nullable=true, options = {"comment" = "Autre structure de rattachement santé de l utilisateur"})
     */
    protected $autreStructureRattacheementSante;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_fonction_etablissement_sante", referencedColumnName="ref_id")
     */
    protected $fonctionEtablissementSante;

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
     *      min = "3",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom de la structure.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom de la structure."
     * )
     * @Nodevo\Javascript(class="validate[minSize[3],maxSize[255]]")
     * @ORM\Column(name="usr_nom_structure", type="string", length=255, nullable=true, options = {"comment" = "Nom de la structure de l utilisateur"})
     */
    protected $nomStructure;
    
    /**
     * @var string
     * @Assert\Length(
     *      min = "3",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans la fonction de la structure.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans la fonction de la structure."
     * )
     * @Nodevo\Javascript(class="validate[minSize[3],maxSize[255]]")
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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->roles    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->objets   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->username = '';
        $this->enabled  = 1;
        $this->sexe     = array();
        $this->civilite = array();
        $this->lock     = false;
        $this->archiver = false;
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
     * Add role
     *
     * @param \Nodevo\RoleBundle\Entity\Role $role
     * @return Project
     */
    public function addRole($role)
    {
        $this->roles[] = $role;
    
        return $this;
    }

    /**
     * Remove role
     *
     * @param \Nodevo\RoleBundle\Entity\Role $role
     */
    public function removeRole($role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Set roles
     *
     * @param \Doctrine\Common\Collections\Collection $roles
     * @return Project
     */
    public function setRoles(array $roles)
    {        
        $this->roles = $roles;
    
        return $this;
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoles()
    {
        return $this->roles->toArray();
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
     * Get autreStructureRattacheementSante
     *
     * @return string $autreStructureRattacheementSante
     */
    public function getAutreStructureRattacheementSante()
    {
        return $this->autreStructureRattacheementSante;
    }
    
    /**
     * Set autreStructureRattacheementSante
     *
     * @param string $autreStructureRattacheementSante
     */
    public function setAutreStructureRattacheementSante($autreStructureRattacheementSante)
    {
        $this->autreStructureRattacheementSante = $autreStructureRattacheementSante;
    }
    
    /**
     * Set fonctionEtablissementSante
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $fonctionEtablissementSante
     */
    public function setFonctionEtablissementSante($fonctionEtablissementSante)
    {
        if($fonctionEtablissementSante instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->fonctionEtablissementSante = $fonctionEtablissementSante;
        else
            $this->fonctionEtablissementSante = null;
    }
    
    /**
     * Get fonctionEtablissementSante
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $fonctionEtablissementSante
     */
    public function getFonctionEtablissementSante()
    {
        return $this->fonctionEtablissementSante;
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
    

    public function getPrenomNom()
    {
        return $this->prenom . ' ' . $this->nom;
    }
}