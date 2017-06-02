<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ForumBundle\Entity\Board;
use HopitalNumerique\ModuleBundle\Entity\Module;
use HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
//Asserts Stuff
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\RoleBundle\Entity\Role;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use Gedmo\Mapping\Annotation as Gedmo;
use Eko\FeedBundle\Item\Writer\RoutedItemInterface;

/**
 * Objet.
 *
 * @ORM\Table(name="hn_objet")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\ObjetRepository")
 * @UniqueEntity(fields="alias", message="Cet alias existe déjà.")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 */
class Objet implements RoutedItemInterface
{
    const FICHIER_1 = 1;
    const FICHIER_2 = 2;
    const FICHIER_EDIT = 3;

    /**
     * @var int
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
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_commentaires", type="boolean", options = {"comment" = "Commentaires autorisés sur l objet ?"})
     */
    private $commentaires;

    /**
     * @var bool
     *
     * @ORM\Column(name="obj_btn_sociaux", type="boolean", options = {"comment" = "Boutons des reseaux sociaux autorisés sur l objet ?"})
     */
    private $btnSociaux;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_notes", type="boolean", options = {"comment" = "Notes autorisées sur l objet ?"})
     */
    private $notes;

    /**
     * @var bool
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
     * @ORM\Column(name="obj_date_modification", type="datetime", nullable=true, options = {"comment" = "Date de modification de l objet"})
     */
    private $dateModification;

    /**
     * @var bool
     *
     * @ORM\Column(name="obj_lock", type="boolean", options = {"comment" = "Verrouillage de l objet ?"})
     */
    private $lock;

    /**
     * @var bool
     *
     * @ORM\Column(name="obj_publication_plus_consulte", type="boolean", options = {"comment" = "Publication affichee dans les plus consultees ?"})
     */
    private $publicationPlusConsulte;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_isInfraDoc", type="boolean", options = {"comment" = "L objet est de type infradocumentaire ?"})
     */
    private $isInfraDoc;

    /**
     * @var bool
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
     * @Gedmo\Versioned
     * @ORM\Column(name="obj_referencement", type="array", options = {"comment" = "Copie du référencement pour l'historique"})
     */
    private $referencement;

    /**
     * @var int
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
     *      joinColumns={ @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")}
     * )
     * @Assert\Count(
     *     min = "1",
     *     minMessage = "Merci de choisir un type au minimum."
     * )
     */
    protected $types;

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
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Contenu", mappedBy="objet", cascade={"persist", "remove" }, orphanRemoval=true)
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
     * Ensemble des notes de maitrise liées à cette publication.
     *
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\RechercheParcoursBundle\Entity\MaitriseUser", mappedBy="objet", cascade={"persist", "remove" })
     */
    protected $maitriseUsers;

    /**
     * @Assert\File(
     *     maxSize = "100M"
     * )
     */
    public $file;

    /**
     * @var int
     *
     * @ORM\Column(name="obj_download_count_1", type="integer", options = {"comment" = "Nombre de téléchargements du fichier 1"})
     */
    protected $downloadCountFile1;

    /**
     * @Assert\File(
     *     maxSize = "100M"
     * )
     */
    public $file2;

    /**
     * @var int
     *
     * @ORM\Column(name="obj_download_count_2", type="integer", options = {"comment" = "Nombre de téléchargements du fichier 2"})
     */
    protected $downloadCountFile2;

    /**
     * @var FichierModifiable
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
     * @Assert\Count(
     *     min = "1",
     *     minMessage = "Merci de choisir un domaine au minimum."
     * )
     */
    protected $domaines;

    /**
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe", inversedBy="publications")
     * @ORM\JoinColumn(name="cp_group_id", referencedColumnName="group_id", nullable=true)
     */
    private $communautePratiqueGroupe;

    /**
     * @var bool
     *
     * @ORM\Column(name="obj_associated_productions", type="boolean", options = {"comment" = "Afficher les ressources associées pour l'objet ?"})
     */
    private $associatedProductions;

    /**
     * @var ArrayCollection|RelatedBoard[]
     *
     * @ORM\OneToMany(targetEntity="RelatedBoard", mappedBy="object", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $relatedBoards;

    /**
     * Initialisation de l'entitée (valeurs par défaut).
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->nbVue = 0;
        $this->downloadCountFile1 = 0;
        $this->downloadCountFile2 = 0;
        $this->commentaires = true;
        $this->notes = true;
        $this->btnSociaux = true;
        $this->associatedProductions = true;
        $this->isInfraDoc = false;
        $this->isArticle = false;
        $this->lock = false;
        $this->vignette = [];
        $this->autodiags = [];
        $this->objets = [];
        $this->referencement = [];
        $this->roles = [];
        $this->types = [];
        $this->ambassadeurs = [];
        $this->modules = [];
        $this->maitriseUsers = [];
        $this->domaines = new ArrayCollection();
        $this->relatedBoards = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $titre
     *
     * @return Objet
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * @param string $alias
     *
     * @return Objet
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $synthese
     *
     * @return Objet
     */
    public function setSynthese($synthese)
    {
        $this->synthese = $synthese;

        return $this;
    }

    /**
     * @return string
     */
    public function getSynthese()
    {
        return $this->synthese;
    }

    /**
     * @param string $resume
     *
     * @return Objet
     */
    public function setResume($resume)
    {
        $this->resume = $resume;

        return $this;
    }

    /**
     * @return string
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * @param string $path
     *
     * @return Objet
     */
    public function setPath($path)
    {
        if (is_null($path) && file_exists($this->getAbsolutePath(self::FICHIER_1))) {
            unlink($this->getAbsolutePath(self::FICHIER_1));
        }

        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path2
     *
     * @return Objet
     */
    public function setPath2($path2)
    {
        if (is_null($path2) && file_exists($this->getAbsolutePath(self::FICHIER_2))) {
            unlink($this->getAbsolutePath(self::FICHIER_2));
        }

        $this->path2 = $path2;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath2()
    {
        return $this->path2;
    }

    /**
     * @param bool $commentaires
     *
     * @return Objet
     */
    public function setCommentaires($commentaires)
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }

    /**
     * @param bool $notes
     *
     * @return Objet
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return bool
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param \DateTime $dateCreation
     *
     * @return Objet
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @param \DateTime $dateModification
     *
     * @return Objet
     */
    public function setDateModification($dateModification)
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateModification()
    {
        return $this->dateModification;
    }

    /**
     * @return bool $lock
     */
    public function getLock()
    {
        return $this->lock;
    }

    /**
     * @param bool $lock
     */
    public function setLock($lock)
    {
        $this->lock = $lock;
    }

    /**
     * @param bool $isInfraDoc
     *
     * @return Objet
     */
    public function setInfraDoc($isInfraDoc)
    {
        if (!$isInfraDoc) {
            $this->getContenus()->clear();
        }

        $this->isInfraDoc = $isInfraDoc;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInfraDoc()
    {
        return $this->isInfraDoc;
    }

    /**
     * @return bool $isArticle
     */
    public function isArticle()
    {
        return $this->isArticle;
    }

    /**
     * @param bool $isArticle
     */
    public function setArticle($isArticle)
    {
        $this->isArticle = $isArticle;
    }

    /**
     * @return string $vignette
     */
    public function getVignette()
    {
        return $this->vignette;
    }

    /**
     * @param string $vignette
     */
    public function setVignette($vignette)
    {
        $this->vignette = $vignette;
    }

    /**
     * @return int $nbVue
     */
    public function getNbVue()
    {
        return $this->nbVue;
    }

    /**
     * @param int $nbVue
     *
     * @return Objet
     */
    public function setNbVue($nbVue)
    {
        $this->nbVue = $nbVue;

        return $this;
    }

    /**
     * Get lockedBy.
     *
     * @return User $lockedBy
     */
    public function getLockedBy()
    {
        return $this->lockedBy;
    }

    /**
     * @param User $lockedBy
     */
    public function setLockedBy($lockedBy)
    {
        if ($lockedBy instanceof User) {
            $this->lockedBy = $lockedBy;
        } else {
            $this->lockedBy = null;
        }
    }

    /**
     * @return Reference $etat
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * @param Reference $etat
     */
    public function setEtat(Reference $etat)
    {
        $this->etat = $etat;
    }

    /**
     * @param Role $role
     *
     * @return Objet
     */
    public function addRole(Role $role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * @param Role $role
     */
    public function removeRole($role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * @param array|Collection $roles
     *
     * @return Objet
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return array|Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param Reference $type
     *
     * @return Objet
     */
    public function addType(Reference $type)
    {
        $this->types[] = $type;

        return $this;
    }

    /**
     * @param Reference $type
     */
    public function removeType(Reference $type)
    {
        $this->types->removeElement($type);
    }

    /**
     * @param array|Collection $types
     *
     * @return Objet
     */
    public function setTypes(array $types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @return array|Collection
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return array<integer>
     */
    public function getTypeIds()
    {
        $typeIds = [];

        foreach ($this->types as $type) {
            $typeIds[] = $type->getId();
        }

        return $typeIds;
    }

    /**
     * @return array $autodiags
     */
    public function getAutodiags()
    {
        return $this->autodiags;
    }

    /**
     * @param array $autodiags
     *
     * @return Objet
     */
    public function setAutodiags(array $autodiags)
    {
        $this->autodiags = $autodiags;

        return $this;
    }

    /**
     * @param int $autodiag
     *
     * @return Objet
     */
    public function addAutodiag($autodiag)
    {
        $this->autodiags[] = $autodiag;

        return $this;
    }

    /**
     * @return array $referencement
     */
    public function getReferencement()
    {
        return $this->referencement;
    }

    /**
     * @param array $referencement
     *
     * @return Objet
     */
    public function setReferencement(array $referencement)
    {
        $this->referencement = $referencement;

        return $this;
    }

    /**
     * @param string $referencement
     *
     * @return $this
     */
    public function addReferencement($referencement)
    {
        $this->referencement[] = $referencement;

        return $this;
    }

    /**
     * @param User $ambassadeur
     *
     * @return Objet
     */
    public function addAmbassadeur(User $ambassadeur)
    {
        $this->ambassadeurs[] = $ambassadeur;

        return $this;
    }

    /**
     * @param User $ambassadeur
     */
    public function removeAmbassadeur(User $ambassadeur)
    {
        $this->ambassadeurs->removeElement($ambassadeur);
    }

    /**
     * @param $ambassadeurs
     *
     * @internal param \HopitalNumerique\UserBundle\Entity\User $ambassadeur
     */
    public function removeAmbassadeurs($ambassadeurs)
    {
        foreach ($ambassadeurs as $ambassadeur) {
            $this->ambassadeurs->removeElement($ambassadeur);
        }
    }

    /**
     * @param array $ambassadeurs
     *
     * @return Objet
     */
    public function setAmbassadeurs(array $ambassadeurs)
    {
        $this->ambassadeurs = $ambassadeurs;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAmbassadeurs()
    {
        return $this->ambassadeurs;
    }

    /**
     * @return Collection
     */
    public function getMaitriseUsers()
    {
        return $this->maitriseUsers;
    }

    /**
     * @param Objet $objet
     *
     * @return Objet
     */
    public function addObjet($objet)
    {
        $this->objets[] = $objet;

        return $this;
    }

    /**
     * @param Objet $objet
     */
    public function removeObjet(Objet $objet)
    {
        $this->objets->removeElement($objet);
    }

    /**
     * @param array|Collection $objets
     *
     * @return Objet
     */
    public function setObjets(array $objets)
    {
        $this->objets = $objets;

        return $this;
    }

    /**
     * @return array|Collection
     */
    public function getObjets()
    {
        return $this->objets;
    }

    /**
     * @return ArrayCollection $consultations
     */
    public function getConsultations()
    {
        return $this->consultations;
    }

    /**
     * @param ArrayCollection $consultations
     *
     * @return Objet
     */
    public function setConsultations(ArrayCollection $consultations)
    {
        $this->consultations = $consultations;

        return $this;
    }

    /**
     * @return ArrayCollection $listeCommentaires
     */
    public function getListeCommentaires()
    {
        return $this->listeCommentaires;
    }

    /**
     * @param ArrayCollection $listeCommentaires
     *
     * @return Objet
     */
    public function setListeCommentaires(ArrayCollection $listeCommentaires)
    {
        $this->listeCommentaires = $listeCommentaires;

        return $this;
    }

    /**
     * @return ArrayCollection $listeNotes
     */
    public function getListeNotes()
    {
        return $this->listeNotes;
    }

    /**
     * @return JSON $listeNotes
     */
    public function getListeNotesJSON()
    {
        return json_encode($this->listeNotes);
    }

    /**
     * @param ArrayCollection $listeNotes
     *
     * @return Objet
     */
    public function setListeNotes(ArrayCollection $listeNotes)
    {
        $this->listeNotes = $listeNotes;

        return $this;
    }

    /**
     * @return ArrayCollection $contenus
     */
    public function getContenus()
    {
        return $this->contenus;
    }

    /**
     * @param ArrayCollection $contenus
     *
     * @return Objet
     */
    public function setContenus(ArrayCollection $contenus)
    {
        $this->contenus = $contenus;

        return $this;
    }

    /**
     * @param int $id
     *
     * @return Contenu|null $contenu
     */
    public function getContenuById($id)
    {
        foreach ($this->contenus as $contenu) {
            if ($contenu->getId() === $id) {
                return $contenu;
            }
        }

        return null;
    }

    /**
     * @param $type
     *
     * @return null|string
     */
    public function getAbsolutePath($type)
    {
        $result = null;

        switch ($type) {
            case self::FICHIER_1:
                if (!is_null($this->path)) {
                    $result = $this->path;
                }
                break;

            case self::FICHIER_2:
                if (!is_null($this->path2)) {
                    $result = $this->path2;
                }
                break;
        }

        if (is_null($result)) {
            return null;
        }

        return $this->getUploadRootDir() . '/' . $result;
    }

    /**
     * @param null $type
     *
     * @return null|string
     */
    public function getWebPath($type = null)
    {
        $result = null;

        if (is_null($type)) {
            if (!is_null($this->path)) {
                $result = $this->path;
            }
        } else {
            switch ($type) {
                case self::FICHIER_1:
                    if (!is_null($this->path)) {
                        $result = $this->path;
                    }
                    break;

                case self::FICHIER_2:
                    if (!is_null($this->path2)) {
                        $result = $this->path2;
                    }
                    break;
            }
        }

        if (is_null($result)) {
            return null;
        }

        return $this->getUploadDir() . '/' . $result;
    }

    /**
     * Fonction qui renvoie le type mime de la piece jointe 1 ou 2.
     */
    public function getTypeMime($type)
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

        if (!$result || is_null($result)) {
            return '';
        }

        return substr($result, strrpos($result, '.') + 1);
    }

    public function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __WEB_DIRECTORY__ . '/' . $this->getUploadDir();
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
        if (null !== $this->file) {
            //delete Old File
            if (file_exists($this->getAbsolutePath(self::FICHIER_1))) {
                unlink($this->getAbsolutePath(self::FICHIER_1));
            }

            $this->path = $this->file->getClientOriginalName();
        }

        if (null !== $this->file2) {
            //delete Old File
            if (file_exists($this->getAbsolutePath(self::FICHIER_2))) {
                unlink($this->getAbsolutePath(self::FICHIER_2));
            }

            $this->path2 = $this->file2->getClientOriginalName();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file && null === $this->file2) {
            return;
        }

        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si erreur il y a

        if (null !== $this->file) {
            $this->file->move($this->getUploadRootDir(), $this->path);
            unset($this->file);
        }

        if (null !== $this->file2) {
            $this->file2->move($this->getUploadRootDir(), $this->path2);
            unset($this->file2);
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($this->getAbsolutePath(self::FICHIER_1) && file_exists($this->getAbsolutePath(self::FICHIER_1))) {
            unlink($this->getAbsolutePath(self::FICHIER_1));
        }

        if ($this->getAbsolutePath(self::FICHIER_2) && file_exists($this->getAbsolutePath(self::FICHIER_2))) {
            unlink($this->getAbsolutePath(self::FICHIER_2));
        }
    }

    /**
     * @param string $dateParution
     *
     * @return Objet
     */
    public function setDateParution($dateParution)
    {
        $this->dateParution = $dateParution;

        return $this;
    }

    /**
     * @return \String
     */
    public function getDateParution()
    {
        return $this->dateParution;
    }

    /**
     * @param bool $isInfraDoc
     *
     * @return Objet
     */
    public function setIsInfraDoc($isInfraDoc)
    {
        $this->isInfraDoc = $isInfraDoc;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsInfraDoc()
    {
        return $this->isInfraDoc;
    }

    /**
     * @param bool $isArticle
     *
     * @return Objet
     */
    public function setIsArticle($isArticle)
    {
        $this->isArticle = $isArticle;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsArticle()
    {
        return $this->isArticle;
    }

    /**
     * @param Consultation $consultations
     *
     * @return Objet
     */
    public function addConsultation(Consultation $consultations)
    {
        $this->consultations[] = $consultations;

        return $this;
    }

    /**
     * @param Consultation $consultations
     */
    public function removeConsultation(Consultation $consultations)
    {
        $this->consultations->removeElement($consultations);
    }

    /**
     * @param Commentaire $listeCommentaires
     *
     * @return Objet
     */
    public function addListeCommentaire(Commentaire $listeCommentaires)
    {
        $this->listeCommentaires[] = $listeCommentaires;

        return $this;
    }

    /**
     * @param Commentaire $listeCommentaires
     */
    public function removeListeCommentaire(Commentaire $listeCommentaires)
    {
        $this->listeCommentaires->removeElement($listeCommentaires);
    }

    /**
     * @param Note $listeNotes
     *
     * @return Objet
     */
    public function addListeNote(Note $listeNotes)
    {
        $this->listeNotes[] = $listeNotes;

        return $this;
    }

    /**
     * @param Note $listeNotes
     */
    public function removeListeNote(Note $listeNotes)
    {
        $this->listeNotes->removeElement($listeNotes);
    }

    /**
     * @param Contenu $contenu
     *
     * @return Objet
     */
    public function addContenus(Contenu $contenu)
    {
        $this->contenus[] = $contenu;

        return $this;
    }

    /**
     * @param Contenu $contenu
     *
     * @return $this
     */
    public function removeContenus(Contenu $contenu)
    {
        $contenuIndex = array_search($contenu, $this->contenus);

        if ($contenuIndex) {
            unset($this->contenus[$contenuIndex]);
        }

        return $this;
    }

    /**
     * @param Module $module
     *
     * @return Objet
     */
    public function addModule(Module $module)
    {
        $this->modules[] = $module;

        return $this;
    }

    /**
     * @param Module $module
     *
     * @return $this
     */
    public function removeModule(Module $module)
    {
        $moduleIndex = array_search($module, $this->modules);

        if ($moduleIndex) {
            unset($this->modules[$moduleIndex]);
        }

        return $this;
    }

    /**
     * Get modules.
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * @param MaitriseUser $maitriseUser
     *
     * @return Objet
     */
    public function addMaitriseUser(MaitriseUser $maitriseUser)
    {
        $this->maitriseUsers[] = $maitriseUser;

        return $this;
    }

    /**
     * @param MaitriseUser $maitriseUser
     */
    public function removeMaitriseUser(MaitriseUser $maitriseUser)
    {
        $this->maitriseUsers->removeElement($maitriseUser);
    }

    /**
     * @param FichierModifiable $fichierModifiable
     *
     * @return Objet
     */
    public function setFichierModifiable(FichierModifiable $fichierModifiable = null)
    {
        $this->fichierModifiable = $fichierModifiable;

        return $this;
    }

    /**
     * @return FichierModifiable
     */
    public function getFichierModifiable()
    {
        return $this->fichierModifiable;
    }

    /**
     * @param Domaine $domaine
     *
     * @return Objet
     */
    public function addDomaine(Domaine $domaine)
    {
        $this->domaines[] = $domaine;

        return $this;
    }

    /**
     * @param Domaine $domaine
     *
     * @return $this
     */
    public function removeDomaine(Domaine $domaine)
    {
        $this->domaines->removeElement($domaine);

        return $this;
    }

    /**
     * @param Collection $domaines
     *
     * @return Objet
     */
    public function setDomaines($domaines)
    {
        $this->domaines = $domaines;

        return $this;
    }

    /**
     * @return ArrayCollection|Domaine[]
     */
    public function getDomaines()
    {
        return $this->domaines;
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
     * @param Groupe $communautePratiqueGroupe
     *
     * @return Objet
     */
    public function setCommunautePratiqueGroupe(Groupe $communautePratiqueGroupe = null)
    {
        $this->communautePratiqueGroupe = $communautePratiqueGroupe;

        return $this;
    }

    /**
     * @return Groupe
     */
    public function getCommunautePratiqueGroupe()
    {
        return $this->communautePratiqueGroupe;
    }

    /**
     * @param bool $alaune
     *
     * @return Objet
     */
    public function setAlaune($alaune)
    {
        $this->alaune = $alaune;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAlaune()
    {
        return $this->alaune;
    }

    /**
     * @param bool $publicationPlusConsulte
     *
     * @return Objet
     */
    public function setPublicationPlusConsulte($publicationPlusConsulte)
    {
        $this->publicationPlusConsulte = $publicationPlusConsulte;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPublicationPlusConsulte()
    {
        return $this->publicationPlusConsulte;
    }

    /**
     * @param bool $btnSociaux
     *
     * @return Objet
     */
    public function setBtnSociaux($btnSociaux)
    {
        $this->btnSociaux = $btnSociaux;

        return $this;
    }

    /**
     * @return bool
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
        return ''; //$this->resume;
    }

    public function getFeedItemPubDate()
    {
        return is_null($this->dateModification) ? $this->dateCreation : $this->dateModification;
    }

    public function getFeedItemRouteName()
    {
        return 'hopital_numerique_publication_publication_objet';
    }

    public function getFeedItemRouteParameters()
    {
        return [
            'id' => $this->id,
            'alias' => $this->alias,
        ];
    }

    public function getFeedItemUrlAnchor()
    {
        return '';
    }

    /**
     * @param Reference $cibleDiffusion
     *
     * @return Objet
     */
    public function setCibleDiffusion(Reference $cibleDiffusion = null)
    {
        $this->cibleDiffusion = $cibleDiffusion;

        return $this;
    }

    /**
     * @return Reference
     */
    public function getCibleDiffusion()
    {
        return $this->cibleDiffusion;
    }

    /**
     * @param string $source
     *
     * @return Objet
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->titre;
    }

    /**
     * @return bool Si point dur
     */
    public function isPointDur()
    {
        foreach ($this->types as $type) {
            if ($type->getId() === Reference::CATEGORIE_OBJET_POINT_DUR_ID) {
                return true;
            }
        }

        return false;
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

    /**
     * @return bool
     */
    public function isAssociatedProductions()
    {
        return $this->associatedProductions;
    }

    /**
     * @param $associatedProductions
     *
     * @return Objet $this
     */
    public function setAssociatedProductions($associatedProductions)
    {
        $this->associatedProductions = $associatedProductions;

        return $this;
    }

    /**
     * @return int
     */
    public function getDownloadCountFile1()
    {
        return $this->downloadCountFile1;
    }

    /**
     * @return $this
     */
    public function incrementDownloadFile1()
    {
        ++$this->downloadCountFile1;

        return $this;
    }

    /**
     * @return int
     */
    public function getDownloadCountFile2()
    {
        return $this->downloadCountFile2;
    }

    /**
     * @return $this
     */
    public function incrementDownloadFile2()
    {
        ++$this->downloadCountFile2;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedBoards()
    {
        return $this->relatedBoards;
    }

    /**
     * @param Board $board
     * @param null  $position
     *
     * @return Objet
     */
    public function linkBoard(Board $board, $position = null)
    {
        foreach ($this->relatedBoards as $relatedBoard) {
            if ($relatedBoard->getBoard()->getId() === $board->getId()) {
                return $this;
            }
        }

        $this->relatedBoards->add(new RelatedBoard($this, $board, $position));

        return $this;
    }

    /**
     * @param RelatedBoard $relatedBoard
     *
     * @return Objet
     */
    public function removeRelatedBoard(RelatedBoard $relatedBoard)
    {
        $this->relatedBoards->removeElement($relatedBoard);

        return $this;
    }
}
