<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Objet
 *
 * @ORM\Table(name="hn_objet")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\ObjetRepository")
 * @UniqueEntity(fields="alias", message="Cet alias existe déjà.")
 * @ORM\HasLifecycleCallbacks
 */
class Objet
{
    const FICHIER_1    = 1;
    const FICHIER_2    = 2;
    const FICHIER_EDIT = 3;

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
     * @Assert\NotBlank(message="Le titre ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage = "Il doit y avoir au moins {{ limit }} caractères dans le titre.",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le titre."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="obj_titre", type="string", length=255, options = {"comment" = "Titre de l objet"})
     */
    private $titre;

    /**
     * @var string
     * @Assert\Length(
     *      max = "255",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le titre."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[255]]")
     * @ORM\Column(name="obj_alias", type="string", length=255, unique=true, options = {"comment" = "Alias de l objet"})
     */
    private $alias;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_synthese", type="text", nullable=true, options = {"comment" = "Synthèse de l objet"})
     */
    private $synthese;

    /**
     * @var string
     * @Assert\NotBlank(message="Le résumé ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @ORM\Column(name="obj_resume", type="text", options = {"comment" = "Résumé de l objet"})
     */
    private $resume;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_path", type="string", length=255, nullable=true, options = {"comment" = "Nom du fichier lié à l objet"})
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_path2", type="string", length=255, nullable=true, options = {"comment" = "Nom du fichier 2 lié à l objet"})
     */
    private $path2;

    /**
     * @var boolean
     *
     * @ORM\Column(name="obj_commentaires", type="boolean", options = {"comment" = "Commentaires autorisés sur l objet ?"})
     */
    private $commentaires;

    /**
     * @var boolean
     *
     * @ORM\Column(name="obj_notes", type="boolean", options = {"comment" = "Notes autorisées sur l objet ?"})
     */
    private $notes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="obj_date_creation", type="datetime", options = {"comment" = "Date de création de l objet"})
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="obj_date_debut_publication", type="datetime", nullable=true, options = {"comment" = "Date de début de la publication de l objet"})
     */
    private $dateDebutPublication;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="obj_date_fin_publication", type="datetime", nullable=true, options = {"comment" = "Date de fin de la publication de l objet"})
     */
    private $dateFinPublication;

    /**
     * @var \DateTime
     *
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
     * @ORM\Column(name="obj_isInfraDoc", type="boolean", options = {"comment" = "L objet est de type infradocumentaire ?"})
     */
    private $isInfraDoc;

    /**
     * @var boolean
     *
     * @ORM\Column(name="obj_isArticle", type="boolean", options = {"comment" = "L objet est un article ?"})
     */
    private $isArticle;

    /**
     * @var string
     * 
     * @ORM\Column(name="obj_vignette", type="string", length=255, options = {"comment" = "Vignette de l objet"}, nullable=true)
     */
    private $vignette;

    /**
     * @var integer
     * 
     * @ORM\Column(name="obj_nb_vue", type="integer", options = {"comment" = "Nombre de fois où lobjet à été vue"})     
     */
    protected $nbVue;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_path_edit", type="string", length=255, nullable=true, options = {"comment" = "Nom du fichier éditable"})
     */
    private $pathEdit;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="obj_locked_by", referencedColumnName="usr_id")
     */
    protected $lockedBy;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="Le statut ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $etat;

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
     * @Assert\File(
     *     maxSize = "10M"
     * )
     */
    public $fileEdit;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->etat         = 3;
        $this->nbVue        = 0;
        $this->commentaires = true;
        $this->notes        = true;
        $this->isInfraDoc   = false;
        $this->isArticle    = false;
        $this->lock         = false;
        $this->vignette     = null;
        $this->roles        = new \Doctrine\Common\Collections\ArrayCollection();
        $this->types        = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ambassadeurs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->modules      = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set pathEdit
     *
     * @param string $pathEdit
     * @return Objet
     */
    public function setPathEdit($pathEdit)
    {
        if( is_null($pathEdit) && file_exists($this->getAbsolutePath( self::FICHIER_EDIT )) )
            unlink($this->getAbsolutePath( self::FICHIER_EDIT ));

        $this->pathEdit = $pathEdit;

        return $this;
    }

    /**
     * Get pathEdit
     *
     * @return string 
     */
    public function getPathEdit()
    {
        return $this->pathEdit;
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

            case self::FICHIER_EDIT:
                if( !is_null($this->pathEdit) )
                    $result = $this->pathEdit;
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
    public function getWebPath( $type )
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

            case self::FICHIER_EDIT:
                if( !is_null($this->pathEdit) )
                    $result = $this->pathEdit;
                break;
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

            case self::FICHIER_EDIT:
                $result = $this->pathEdit;
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

        if (null !== $this->fileEdit){
            //delete Old File
            if ( file_exists($this->getAbsolutePath( self::FICHIER_EDIT )) )
                unlink($this->getAbsolutePath( self::FICHIER_EDIT ));

            $this->pathEdit = $this->fileEdit->getClientOriginalName();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if ( null === $this->file && null === $this->file2 && null === $this->fileEdit )
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

        if ( null !== $this->fileEdit ){
            $this->fileEdit->move($this->getUploadRootDir(), $this->pathEdit);
            unset($this->fileEdit);
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

        if ( $fileEdit = $this->getAbsolutePath( self::FICHIER_EDIT ) && file_exists( $this->getAbsolutePath( self::FICHIER_EDIT ) ) )
            unlink($fileEdit);
    }
}
