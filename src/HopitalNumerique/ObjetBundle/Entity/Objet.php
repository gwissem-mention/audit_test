<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use Gedmo\Mapping\Annotation as Gedmo;

use Eko\FeedBundle\Item\Writer\RoutedItemInterface;

/**
 * Objet
 *
 * @ORM\Table(name="hn_objet")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\ObjetRepository")
 * @UniqueEntity(fields="alias", message="Cet alias existe déjà.")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 */
class Objet implements RoutedItemInterface
{
    const FICHIER_1    = 1;
    const FICHIER_2    = 2;
    const FICHIER_EDIT = 3;
    
    /**
     * @var ID de l'article de la communauté de pratique.
     */
    const ARTICLE_COMMUNAUTE_PRATIQUE_ID = 1000;


    /**
     * @var integer
     *
     * @ORM\Column(name="obj_id", type="integer", options = {"comment" = "ID de l objet"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * 
     * @Assert\NotBlank(message="Le titre ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage = "Il doit y avoir au moins {{ limit }} caractères dans le titre.",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le titre."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_titre", type="string", length=255, options = {"comment" = "Titre de l objet"})
     */
    private $titre;

    /**
     * @var string
     * 
     * @Assert\Length(
     *      max = "255",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le titre."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[255]]")
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_alias", type="string", length=255, unique=true, options = {"comment" = "Alias de l objet"})
     */
    private $alias;

    /**
     * @var string
     * 
     * @Assert\Length(
     *      max = "255",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans la source."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[255]]")
     * @ORM\Column(name="obj_source", type="string", nullable=true, length=255, options = {"comment" = "Source si externe"})
     */
    private $source;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_synthese", type="text", nullable=true, options = {"comment" = "Synthèse de l objet"})
     */
    private $synthese;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Le résumé ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_resume", type="text", options = {"comment" = "Résumé de l objet"})
     */
    private $resume;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_path", type="string", length=255, nullable=true, options = {"comment" = "Nom du fichier lié à l objet"})
     */
    private $path;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_path2", type="string", length=255, nullable=true, options = {"comment" = "Nom du fichier 2 lié à l objet"})
     */
    private $path2;

    /**
     * @var boolean
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_commentaires", type="boolean", options = {"comment" = "Commentaires autorisés sur l objet ?"})
     */
    private $commentaires;

    /**
     * @var boolean
     *
     * @ORM\Column(name="obj_btn_sociaux", type="boolean", options = {"comment" = "Boutons des reseaux sociaux autorisés sur l objet ?"})
     */
    private $btnSociaux;

    /**
     * @var boolean
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_notes", type="boolean", options = {"comment" = "Notes autorisées sur l objet ?"})
     */
    private $notes;

    /**
     * @var boolean
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_alaune", type="boolean", options = {"comment" = "A la une ?"})
     */
    private $alaune;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="obj_date_creation", type="datetime", options = {"comment" = "Date de création de l objet"})
     */
    private $dateCreation;

    /**
     * @var \String
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_date_debut_parution", type="string", length=255, nullable=true, options = {"comment" = "Date de parution de l objet"})
     */
    private $dateParution;
    
    /**
     * @var \DateTime
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_date_debut_publication", type="datetime", nullable=true, options = {"comment" = "Date de début de la publication de l objet"})
     */
    private $dateDebutPublication;

    /**
     * @var \DateTime
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_date_fin_publication", type="datetime", nullable=true, options = {"comment" = "Date de fin de la publication de l objet"})
     */
    private $dateFinPublication;

    /**
     * @var \DateTime
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_date_modification", type="datetime", nullable=true, options = {"comment" = "Date de modification de l objet"})
     */
    private $dateModification;

    /**
     * @var boolean
     *
     * @ORM\Column(name="obj_lock", type="boolean", options = {"comment" = "Verrouillage de l objet ?"})
     */
    private $lock;

    /**
     * @var boolean
     *
     * @ORM\Column(name="obj_publication_plus_consulte", type="boolean", options = {"comment" = "Publication affichee dans les plus consultees ?"})
     */
    private $publicationPlusConsulte;

    /**
     * @var boolean
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_isInfraDoc", type="boolean", options = {"comment" = "L objet est de type infradocumentaire ?"})
     */
    private $isInfraDoc;

    /**
     * @var boolean
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_isArticle", type="boolean", options = {"comment" = "L objet est un article ?"})
     */
    private $isArticle;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_vignette", type="string", length=255, options = {"comment" = "Vignette de l objet"}, nullable=true)
     */
    private $vignette;

    /**
     * @var array
     *
     * @ORM\Column(name="obj_autodiag", type="array", options = {"comment" = "Liste des autodiag liés à l objet"})
     */
    private $autodiags;

    /**
     * @var array
     *
     * @ORM\Column(name="obj_objets", type="array", options = {"comment" = "Liste des productions liés à l objet"})
     */
    private $objets;

    /**
     * @var array
     *
     * @ORM\Column(name="obj_glossaires", type="array", options = {"comment" = "Mots du glossaire liés à l objet"})
     */
    private $glossaires;

    /**
     * @var array
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_referencement", type="array", options = {"comment" = "Copie du référencement pour l historique"})
     */
    private $referencement;

    /**
     * @var integer
     * 
     * @ORM\Column(name="obj_nb_vue", type="integer", options = {"comment" = "Nombre de fois où lobjet à été vue"})     
     */
    protected $nbVue;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="obj_locked_by", referencedColumnName="usr_id", onDelete="SET NULL")
     */
    protected $lockedBy;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="Le statut ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @Gedmo\Versioned
     */
    protected $etat;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_cible_diffusion", referencedColumnName="ref_id")
     */
    protected $cibleDiffusion;

    /**
     * @ORM\ManyToMany(targetEntity="\Nodevo\RoleBundle\Entity\Role")
     * @ORM\JoinTable(name="hn_objet_role",
     *      joinColumns={ @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ro_id", referencedColumnName="ro_id")}
     * )
     */
    protected $roles;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_objet_type",
     *      joinColumns={ @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")}
     * )
     * @Assert\NotBlank(message="Merci de choisir un type au minimum.")
     */
    protected $types;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\RefObjet", mappedBy="objet", cascade={"persist", "remove" })
     */
    protected $references;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Consultation", mappedBy="objet", cascade={"persist", "remove" })
     */
    protected $consultations;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Commentaire", mappedBy="objet", cascade={"persist", "remove" })
     */
    protected $listeCommentaires;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Note", mappedBy="objet", cascade={"persist", "remove" })
     */
    protected $listeNotes;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Contenu", mappedBy="objet", cascade={"persist", "remove" })
     */
    protected $contenus;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\UserBundle\Entity\User", inversedBy="objets")
     * @ORM\JoinTable(name="hn_objet_ambassadeur",
     *      joinColumns={ @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id")}
     * )
     */
    protected $ambassadeurs;
    
    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ModuleBundle\Entity\Module", mappedBy="productions")
     */
    protected $modules;

    /**
     * Ensemble des notes de maitrise liées à cette publication
     *
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser", mappedBy="objet", cascade={"persist", "remove" })
     */
    protected $maitriseUsers;

    /**
     * @Assert\File(
     *     maxSize = "10M"
     * )
     */
    public $file;
    
    /**
     * @Assert\File(
     *     maxSize = "10M"
     * )
     */
    public $file2;

    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="FichierModifiable", inversedBy="objet")
     * @ORM\JoinColumn(name="ofm_id", referencedColumnName="ofm_id")
     */
    protected $fichierModifiable;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(name="hn_domaine_gestions_objet",
     *      joinColumns={ @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    /**
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe", inversedBy="publications")
     * @ORM\JoinColumn(name="cp_group_id", referencedColumnName="group_id", nullable=true)
     */
    private $communautePratiqueGroupe;

    /**
     * @ORM\ManyToMany(targetEntity="Contenu", mappedBy="infradocs")
     */
    private $infradocContenus;


    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->dateCreation  = new \DateTime();
        $this->nbVue         = 0;
        $this->commentaires  = true;
        $this->notes         = true;
        $this->btnSociaux    = true;
        $this->isInfraDoc    = false;
        $this->isArticle     = false;
        $this->lock          = false;
        $this->vignette      = array();
        $this->autodiags     = array();
        $this->objets        = array();
        $this->glossaires    = array();
        $this->referencement = array();
        $this->roles         = array();
        $this->types         = array();
        $this->ambassadeurs  = array();
        $this->modules       = array();
        $this->maitriseUsers = array();
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
     * Set titre
     *
     * @param string $titre
     * @return Objet
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set alias
     *
     * @param string $alias
     * @return Objet
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string 
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set synthese
     *
     * @param string $synthese
     * @return Objet
     */
    public function setSynthese($synthese)
    {
        $this->synthese = $synthese;

        return $this;
    }

    /**
     * Get synthese
     *
     * @return string 
     */
    public function getSynthese()
    {
        return $this->synthese;
    }

    /**
     * Set resume
     *
     * @param string $resume
     * @return Objet
     */
    public function setResume($resume)
    {
        $this->resume = $resume;

        return $this;
    }

    /**
     * Get resume
     *
     * @return string 
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Objet
     */
    public function setPath($path)
    {
        if( is_null($path) && file_exists($this->getAbsolutePath( self::FICHIER_1 )) )
            unlink($this->getAbsolutePath( self::FICHIER_1 ));

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

    /**
     * Set path2
     *
     * @param string $path2
     * @return Objet
     */
    public function setPath2($path2)
    {
        if( is_null($path2) && file_exists($this->getAbsolutePath( self::FICHIER_2 )) )
            unlink($this->getAbsolutePath( self::FICHIER_2 ));

        $this->path2 = $path2;

        return $this;
    }

    /**
     * Get path2
     *
     * @return string 
     */
    public function getPath2()
    {
        return $this->path2;
    }

    /**
     * Set commentaires
     *
     * @param boolean $commentaires
     * @return Objet
     */
    public function setCommentaires($commentaires)
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    /**
     * Get commentaires
     *
     * @return boolean 
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }

    /**
     * Set notes
     *
     * @param boolean $notes
     * @return Objet
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return boolean 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Objet
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
     * Set dateDebutPublication
     *
     * @param \DateTime $dateDebutPublication
     * @return Objet
     */
    public function setDateDebutPublication($dateDebutPublication)
    {
        $this->dateDebutPublication = $dateDebutPublication;

        return $this;
    }

    /**
     * Get dateDebutPublication
     *
     * @return \DateTime 
     */
    public function getDateDebutPublication()
    {
        return $this->dateDebutPublication;
    }

    /**
     * Set dateFinPublication
     *
     * @param \DateTime $dateFinPublication
     * @return Objet
     */
    public function setDateFinPublication($dateFinPublication)
    {
        $this->dateFinPublication = $dateFinPublication;

        return $this;
    }

    /**
     * Get dateFinPublication
     *
     * @return \DateTime 
     */
    public function getDateFinPublication()
    {
        return $this->dateFinPublication;
    }

    /**
     * Set dateModification
     *
     * @param \DateTime $dateModification
     * @return Objet
     */
    public function setDateModification($dateModification)
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    /**
     * Get dateModification
     *
     * @return \DateTime 
     */
    public function getDateModification()
    {
        return $this->dateModification;
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
     * Set isInfraDoc
     *
     * @param boolean $isInfraDoc
     * @return Objet
     */
    public function setInfraDoc($isInfraDoc)
    {
        $this->isInfraDoc = $isInfraDoc;

        return $this;
    }

    /**
     * Get isInfraDoc
     *
     * @return boolean 
     */
    public function isInfraDoc()
    {
        return $this->isInfraDoc;
    }

    /**
     * Get isArticle
     *
     * @return boolean $isArticle
     */
    public function isArticle()
    {
        return $this->isArticle;
    }
    
    /**
     * Set isArticle
     *
     * @param boolean $isArticle
     */
    public function setArticle($isArticle)
    {
        $this->isArticle = $isArticle;
    }
    
    /**
     * Get vignette
     *
     * @return string $vignette
     */
    public function getVignette()
    {
        return $this->vignette;
    }
    
    /**
     * Set vignette
     *
     * @param string $vignette
     */
    public function setVignette($vignette)
    {
        $this->vignette = $vignette;
    }

    /**
     * Get nbVue
     *
     * @return integer $nbVue
     */
    public function getNbVue()
    {
        return $this->nbVue;
    }
    
    /**
     * Set nbVue
     *
     * @param integer $nbVue
     */
    public function setNbVue($nbVue)
    {
        $this->nbVue = $nbVue;
        return $this;
    }

    /**
     * Get lockedBy
     *
     * @return \HopitalNumerique\UserBundle\Entity\User $lockedBy
     */
    public function getLockedBy()
    {
        return $this->lockedBy;
    }
    
    /**
     * Set lockedBy
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $lockedBy
     */
    public function setLockedBy($lockedBy)
    {
        if( $lockedBy instanceof \HopitalNumerique\UserBundle\Entity\User )
            $this->lockedBy = $lockedBy;
        else
            $this->lockedBy = null;
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
    public function setEtat(\HopitalNumerique\ReferenceBundle\Entity\Reference $etat)
    {
        $this->etat = $etat;
    }

    /**
     * Add role
     *
     * @param \Nodevo\RoleBundle\Entity\Role $role
     * @return Objet
     */
    public function addRole(\Nodevo\RoleBundle\Entity\Role $role)
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
     * @return Objet
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
        return $this->roles;
    }

    /**
     * Add type
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $type
     * @return Objet
     */
    public function addType(\HopitalNumerique\ReferenceBundle\Entity\Reference $type)
    {
        $this->types[] = $type;
    
        return $this;
    }

    /**
     * Remove type
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $type
     */
    public function removeType(\HopitalNumerique\ReferenceBundle\Entity\Reference $type)
    {
        $this->types->removeElement($type);
    }

    /**
     * Set types
     *
     * @param \Doctrine\Common\Collections\Collection $types
     * @return Objet
     */
    public function setTypes(array $types)
    {        
        $this->types = $types;
    
        return $this;
    }

    /**
     * Get types
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Get autodiags
     *
     * @return array $autodiags
     */
    public function getAutodiags()
    {
        return $this->autodiags;
    }
    
    /**
     * Set autodiags
     *
     * @param array $autodiags
     */
    public function setAutodiags(array $autodiags)
    {
        $this->autodiags = $autodiags;
        return $this;
    }
    
    /**
     * add autodiag
     *
     * @param integer $autodiag
     */
    public function addAutodiag($autodiag)
    {
        $this->autodiags[] = $autodiag;
        return $this;
    }
    
    /**
     * Get glossaires
     *
     * @return array $glossaires
     */
    public function getGlossaires()
    {
        return $this->glossaires;
    }
    
    /**
     * Set glossaires
     *
     * @param array $glossaires
     */
    public function setGlossaires(array $glossaires)
    {
        $this->glossaires = $glossaires;
        return $this;
    }

    /**
     * Remove glossaire
     *
     * @param string $glossaire
     */
    public function removeGlossaire($glossaire)
    {
        $this->glossaires->removeElement($glossaire);
    }
    
    /**
     * add glossaire
     *
     * @param string $glossaire
     */
    public function addGlossaire($glossaire)
    {
        $this->glossaires[] = $glossaire;
        return $this;
    }

    /**
     * Get referencement
     *
     * @return array $referencement
     */
    public function getReferencement()
    {
        return $this->referencement;
    }
    
    /**
     * Set referencement
     *
     * @param array $referencement
     */
    public function setReferencement(array $referencement)
    {
        $this->referencement = $referencement;
        return $this;
    }
    
    /**
     * add referencement
     *
     * @param string $referencement
     */
    public function addReferencement($referencement)
    {
        $this->referencement[] = $referencement;
        return $this;
    }

    /**
     * Add ambassadeur
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur
     * @return Objet
     */
    public function addAmbassadeur(\HopitalNumerique\UserBundle\Entity\User $ambassadeur)
    {
        $this->ambassadeurs[] = $ambassadeur;
    
        return $this;
    }

    /**
     * Remove ambassadeur
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur
     */
    public function removeAmbassadeur(\HopitalNumerique\UserBundle\Entity\User $ambassadeur)
    {
        $this->ambassadeurs->removeElement($ambassadeur);
    }

    /**
     * Remove ambassadeurs
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur
     */
    public function removeAmbassadeurs($ambassadeurs)
    {
        foreach ($ambassadeurs as $ambassadeur)
        {
            $this->ambassadeurs->removeElement($ambassadeur);
        }
    }

    /**
     * Set ambassadeurs
     *
     * @param \Doctrine\Common\Collections\Collection $ambassadeurs
     * @return Objet
     */
    public function setAmbassadeurs(array $ambassadeurs)
    {        
        $this->ambassadeurs = $ambassadeurs;
    
        return $this;
    }

    /**
     * Get ambassadeurs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAmbassadeurs()
    {
        return $this->ambassadeurs;
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMaitriseUsers()
    {
        return $this->maitriseUsers;
    }

    /**
     * Add objet
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $objet
     * @return Objet
     */
    public function addObjet($objet)
    {
        $this->objets[] = $objet;
    
        return $this;
    }

    /**
     * Remove objet
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $objet
     */
    public function removeObjet(\HopitalNumerique\ObjetBundle\Entity\Objet $objet)
    {
        $this->objets->removeElement($objet);
    }

    /**
     * Set objets
     *
     * @param \Doctrine\Common\Collections\Collection $objets
     * @return Objet
     */
    public function setObjets(array $objets)
    {        
        $this->objets = $objets;
    
        return $this;
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
     * Get references
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $references
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Set references
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $references
     * @return Objet
     */
    public function setReferences(\Doctrine\Common\Collections\ArrayCollection $references)
    {        
        $this->references = $references;
    
        return $this;
    }

    /**
     * Get consultations
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $consultations
     */
    public function getConsultations()
    {
        return $this->consultations;
    }

    /**
     * Set consultations
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $consultations
     * @return Objet
     */
    public function setConsultations(\Doctrine\Common\Collections\ArrayCollection $consultations)
    {        
        $this->consultations = $consultations;
    
        return $this;
    }

    /**
     * Get listeCommentaires
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $listeCommentaires
     */
    public function getListeCommentaires()
    {
        return $this->listeCommentaires;
    }

    /**
     * Set listeCommentaires
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $listeCommentaires
     * @return Objet
     */
    public function setListeCommentaires(\Doctrine\Common\Collections\ArrayCollection $listeCommentaires)
    {        
        $this->listeCommentaires = $listeCommentaires;
    
        return $this;
    }

    /**
     * Get listeNotes
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $listeNotes
     */
    public function getListeNotes()
    {
        return $this->listeNotes;
    }

    /**
     * Get listeNotes JSONifié
     *
     * @return JSON $listeNotes
     */
    public function getListeNotesJSON()
    {
        return json_encode($this->listeNotes);
    }

    /**
     * Set listeNotes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $listeNotes
     * @return Objet
     */
    public function setListeNotes(\Doctrine\Common\Collections\ArrayCollection $listeNotes)
    {        
        $this->listeNotes = $listeNotes;
    
        return $this;
    }

    /**
     * Get contenus
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $contenus
     */
    public function getContenus()
    {
        return $this->contenus;
    }

    /**
     * Set contenus
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $contenus
     * @return Objet
     */
    public function setContenus(\Doctrine\Common\Collections\ArrayCollection $contenus)
    {        
        $this->contenus = $contenus;
    
        return $this;
    }

    /**
     * Get a contenu by id ou null si pas trouvé
     *
     * @param int $id
     * @return Contenu|null $contenu
     */
    public function getContenuById($id)
    {
        foreach ($this->contenus as $contenu) 
        {
            if($contenu->getId() === $id)
                return $contenu;
        }

        return null;
    }

    /**
     * [getAbsolutePath description]
     *
     * @param  [type] $type [description]
     *
     * @return [type]
     */
    public function getAbsolutePath( $type )
    {
        $result = null;

        switch ($type) {
            case self::FICHIER_1:
                if( !is_null($this->path) )
                    $result = $this->path;
                break;

            case self::FICHIER_2:
                if( !is_null($this->path2) )
                    $result = $this->path2;
                break;
        }

        if( is_null($result) )
            return null;

        return $this->getUploadRootDir() . '/' . $result;
    }

    /**
     * [getWebPath description]
     *
     * @param  [type] $type [description]
     *
     * @return [type]
     */
    public function getWebPath( $type = null )
    {
        $result = null;

        if(is_null($type))
        {
            if( !is_null($this->path) )
            {
                $result = $this->path;
            }
        }
        else
        {
            switch ($type) {
                case self::FICHIER_1:
                    if( !is_null($this->path) )
                        $result = $this->path;
                    break;

                case self::FICHIER_2:
                    if( !is_null($this->path2) )
                        $result = $this->path2;
                    break;
            }
        }

        
        if( is_null($result) )
            return null;

        return $this->getUploadDir() . '/' . $result;
    }
    
    /**
     * Fonction qui renvoie le type mime de la piece jointe 1 ou 2
     */
    public function getTypeMime( $type )
    {
        $result = null;

        switch ($type) {
            case self::FICHIER_1:
                $result = $this->path;
                break;

            case self::FICHIER_2:
                $result = $this->path2;
                break;
        }

        if( !$result || is_null($result) )
            return "";
        
        return substr($result, strrpos($result, ".") + 1);
    }

    public function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __WEB_DIRECTORY__.'/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'medias/Objets/Fichiers';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file){
            //delete Old File
            if ( file_exists($this->getAbsolutePath( self::FICHIER_1 )) )
                unlink($this->getAbsolutePath( self::FICHIER_1 ));

            $this->path = $this->file->getClientOriginalName();
        }

        if (null !== $this->file2){
            //delete Old File
            if ( file_exists($this->getAbsolutePath( self::FICHIER_2 )) )
                unlink($this->getAbsolutePath( self::FICHIER_2 ));

            $this->path2 = $this->file2->getClientOriginalName();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if ( null === $this->file && null === $this->file2 )
            return;
        
        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si erreur il y a   
        
        if ( null !== $this->file ){        
            $this->file->move($this->getUploadRootDir(), $this->path);
            unset($this->file);
        }

        if ( null !== $this->file2 ){
            $this->file2->move($this->getUploadRootDir(), $this->path2);
            unset($this->file2);
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ( $file = $this->getAbsolutePath( self::FICHIER_1 ) && file_exists( $this->getAbsolutePath( self::FICHIER_1 ) ) )
            unlink($file);

        if ( $file2 = $this->getAbsolutePath( self::FICHIER_2 ) && file_exists( $this->getAbsolutePath( self::FICHIER_2 ) ) )
            unlink($file2);
    }

    /**
     * Set dateParution
     *
     * @param String $dateParution
     * @return Objet
     */
    public function setDateParution($dateParution)
    {
        $this->dateParution = $dateParution;

        return $this;
    }

    /**
     * Get dateParution
     *
     * @return \String 
     */
    public function getDateParution()
    {
        return $this->dateParution;
    }

    /**
     * Set isInfraDoc
     *
     * @param boolean $isInfraDoc
     * @return Objet
     */
    public function setIsInfraDoc($isInfraDoc)
    {
        $this->isInfraDoc = $isInfraDoc;

        return $this;
    }

    /**
     * Get isInfraDoc
     *
     * @return boolean 
     */
    public function getIsInfraDoc()
    {
        return $this->isInfraDoc;
    }

    /**
     * Set isArticle
     *
     * @param boolean $isArticle
     * @return Objet
     */
    public function setIsArticle($isArticle)
    {
        $this->isArticle = $isArticle;

        return $this;
    }

    /**
     * Get isArticle
     *
     * @return boolean 
     */
    public function getIsArticle()
    {
        return $this->isArticle;
    }

    /**
     * Add references
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\RefObjet $references
     * @return Objet
     */
    public function addReference(\HopitalNumerique\ObjetBundle\Entity\RefObjet $references)
    {
        $this->references[] = $references;

        return $this;
    }

    /**
     * Remove references
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\RefObjet $references
     */
    public function removeReference(\HopitalNumerique\ObjetBundle\Entity\RefObjet $references)
    {
        $this->references->removeElement($references);
    }

    /**
     * Add consultations
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Consultation $consultations
     * @return Objet
     */
    public function addConsultation(\HopitalNumerique\ObjetBundle\Entity\Consultation $consultations)
    {
        $this->consultations[] = $consultations;

        return $this;
    }

    /**
     * Remove consultations
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Consultation $consultations
     */
    public function removeConsultation(\HopitalNumerique\ObjetBundle\Entity\Consultation $consultations)
    {
        $this->consultations->removeElement($consultations);
    }

    /**
     * Add listeCommentaires
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Commentaire $listeCommentaires
     * @return Objet
     */
    public function addListeCommentaire(\HopitalNumerique\ObjetBundle\Entity\Commentaire $listeCommentaires)
    {
        $this->listeCommentaires[] = $listeCommentaires;

        return $this;
    }

    /**
     * Remove listeCommentaires
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Commentaire $listeCommentaires
     */
    public function removeListeCommentaire(\HopitalNumerique\ObjetBundle\Entity\Commentaire $listeCommentaires)
    {
        $this->listeCommentaires->removeElement($listeCommentaires);
    }

    /**
     * Add listeNotes
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Note $listeNotes
     * @return Objet
     */
    public function addListeNote(\HopitalNumerique\ObjetBundle\Entity\Note $listeNotes)
    {
        $this->listeNotes[] = $listeNotes;

        return $this;
    }

    /**
     * Remove listeNotes
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Note $listeNotes
     */
    public function removeListeNote(\HopitalNumerique\ObjetBundle\Entity\Note $listeNotes)
    {
        $this->listeNotes->removeElement($listeNotes);
    }

    /**
     * Add contenus
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $contenus
     * @return Objet
     */
    public function addContenus(\HopitalNumerique\ObjetBundle\Entity\Contenu $contenus)
    {
        $this->contenus[] = $contenus;

        return $this;
    }

    /**
     * Remove contenus
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $contenus
     */
    public function removeContenus(\HopitalNumerique\ObjetBundle\Entity\Contenu $contenus)
    {
        $this->contenus->removeElement($contenus);
    }

    /**
     * Add modules
     *
     * @param \HopitalNumerique\ModuleBundle\Entity\Module $modules
     * @return Objet
     */
    public function addModule(\HopitalNumerique\ModuleBundle\Entity\Module $modules)
    {
        $this->modules[] = $modules;

        return $this;
    }

    /**
     * Remove modules
     *
     * @param \HopitalNumerique\ModuleBundle\Entity\Module $modules
     */
    public function removeModule(\HopitalNumerique\ModuleBundle\Entity\Module $modules)
    {
        $this->modules->removeElement($modules);
    }

    /**
     * Get modules
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Add maitriseUsers
     *
     * @param \HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser $maitriseUsers
     * @return Objet
     */
    public function addMaitriseUser(\HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser $maitriseUsers)
    {
        $this->maitriseUsers[] = $maitriseUsers;

        return $this;
    }

    /**
     * Remove maitriseUsers
     *
     * @param \HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser $maitriseUsers
     */
    public function removeMaitriseUser(\HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser $maitriseUsers)
    {
        $this->maitriseUsers->removeElement($maitriseUsers);
    }

    /**
     * Set fichierModifiable
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\FichierModifiable $fichierModifiable
     * @return Objet
     */
    public function setFichierModifiable(\HopitalNumerique\ObjetBundle\Entity\FichierModifiable $fichierModifiable = null)
    {
        $this->fichierModifiable = $fichierModifiable;

        return $this;
    }

    /**
     * Get fichierModifiable
     *
     * @return \HopitalNumerique\ObjetBundle\Entity\FichierModifiable 
     */
    public function getFichierModifiable()
    {
        return $this->fichierModifiable;
    }

    /**
     * Add domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     * @return Objet
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines[] = $domaines;

        return $this;
    }

    /**
     * Remove domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines->removeElement($domaines);
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
     * Set communautePratiqueGroupe
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupe
     * @return Objet
     */
    public function setCommunautePratiqueGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $communautePratiqueGroupe = null)
    {
        $this->communautePratiqueGroupe = $communautePratiqueGroupe;

        return $this;
    }

    /**
     * Get communautePratiqueGroupe
     *
     * @return \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe 
     */
    public function getCommunautePratiqueGroupe()
    {
        return $this->communautePratiqueGroupe;
    }

    /**
     * Set alaune
     *
     * @param boolean $alaune
     * @return Objet
     */
    public function setAlaune($alaune)
    {
        $this->alaune = $alaune;

        return $this;
    }

    /**
     * Get alaune
     *
     * @return boolean 
     */
    public function getAlaune()
    {
        return $this->alaune;
    }

    /**
     * Set publicationPlusConsulte
     *
     * @param boolean $publicationPlusConsulte
     * @return Objet
     */
    public function setPublicationPlusConsulte($publicationPlusConsulte)
    {
        $this->publicationPlusConsulte = $publicationPlusConsulte;

        return $this;
    }

    /**
     * Get publicationPlusConsulte
     *
     * @return boolean 
     */
    public function getPublicationPlusConsulte()
    {
        return $this->publicationPlusConsulte;
    }

    /**
     * Set btnSociaux
     *
     * @param boolean $btnSociaux
     * @return Objet
     */
    public function setBtnSociaux($btnSociaux)
    {
        $this->btnSociaux = $btnSociaux;

        return $this;
    }

    /**
     * Get btnSociaux
     *
     * @return boolean 
     */
    public function getBtnSociaux()
    {
        return $this->btnSociaux;
    }

    
    // vvvv     Flux RSS      vvvv
    
    public function getFeedItemTitle()
    {
        return $this->titre;
    }

    public function getFeedItemDescription()
    {
        return "";//$this->resume;
    }

    public function getFeedItemPubDate()
    {
        return is_null($this->dateModification) ? $this->dateCreation : $this->dateModification ;
    }

    public function getFeedItemRouteName()
    {
        return 'hopital_numerique_publication_publication_objet';
    }

    public function getFeedItemRouteParameters()
    {
        return array(
            'id'    => $this->id,
            'alias' => $this->alias
        );
    }

    public function getFeedItemUrlAnchor()
    {
        return "";
    }


    /**
     * Set cibleDiffusion
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $cibleDiffusion
     * @return Objet
     */
    public function setCibleDiffusion(\HopitalNumerique\ReferenceBundle\Entity\Reference $cibleDiffusion = null)
    {
        $this->cibleDiffusion = $cibleDiffusion;

        return $this;
    }

    /**
     * Get cibleDiffusion
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getCibleDiffusion()
    {
        return $this->cibleDiffusion;
    }

    /**
     * Set source
     *
     * @param string $source
     * @return Objet
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Add infradocContenus
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $infradocContenus
     *
     * @return Objet
     */
    public function addInfradocContenus(\HopitalNumerique\ObjetBundle\Entity\Contenu $infradocContenus)
    {
        $this->infradocContenus[] = $infradocContenus;

        return $this;
    }

    /**
     * Remove infradocContenus
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $infradocContenus
     */
    public function removeInfradocContenus(\HopitalNumerique\ObjetBundle\Entity\Contenu $infradocContenus)
    {
        $this->infradocContenus->removeElement($infradocContenus);
    }

    /**
     * Get infradocContenus
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInfradocContenus()
    {
        return $this->infradocContenus;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->titre;
    }

    /**
     * Retourne les libellés des types.
     *
     * @return array<string> Libellés
     */
    public function getTypeLabels()
    {
        $typeLabels = [];

        foreach ($this->types as $type) {
            $typeLabels[] = $type->getLibelle();
        }

        return $typeLabels;
    }

    /**
     * Retourne le résumé du résumé (contenu de l'objet).
     *
     * @return string
     */
    public function getResumeResume()
    {
        if (false !== strpos($this->resume, '<!-- pagebreak -->')) {
            $resumeExplode = explode('<!-- pagebreak -->', $this->resume);
            return html_entity_decode(strip_tags($resumeExplode[0]), 2 | 0, 'UTF-8');
        }

        return $this->resume;
    }
}
