<?php

namespace HopitalNumerique\ModuleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Session
 *
 * @ORM\Table(name="hn_module_session")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ModuleBundle\Repository\SessionRepository")
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Session
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ses_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Module", inversedBy="sessions")
     * @ORM\JoinColumn(name="mod_module", referencedColumnName="mod_id")
     */
    protected $module;
    
    /**
     * @var \DateTime
     *
     * @Assert\NotBlank(message="La date de la session ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required,custom[date]]")
     * @ORM\Column(name="ses_date_session", type="datetime", options = {"comment" = "Date de la Session"})
     */
    protected $dateSession;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank(message="La date d'ouverture des inscriptions de la session ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required,custom[date]]")
     * @ORM\Column(name="ses_dateOuvertureInscription", type="datetime")
     */
    protected $dateOuvertureInscription;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank(message="La date de fermeture des inscriptions de la session ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required,custom[date]]")
     * @ORM\Column(name="ses_dateFermetureInscription", type="datetime")
     */
    protected $dateFermetureInscription;

    /**
     * 
     * @Assert\NotBlank(message="La durée ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * 
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_duree", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="duree.libelle", options = {"comment" = "Durée pointant sur la table référence avec le code DUREE_FORMATION de la session"})
     */
    protected $duree;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Les horaires ne peuvent pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * 
     * @ORM\Column(name="ses_horaires", type="text", options = {"comment" = "Horaires de la session"})
     */
    protected $horaires;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Le lieu ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * 
     * @ORM\Column(name="ses_lieu", type="text", options = {"comment" = "Lieu de la session"})
     */
    protected $lieu;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="La description ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @ORM\Column(name="ses_description", type="text", options = {"comment" = "Description de la session"})
     */
    protected $description;

    /**
     * @var integer
     *
     * @Nodevo\Javascript(class="validate[custom[integer],min[0]]")
     * @ORM\Column(name="ses_nombrePlaceDisponible", type="integer", nullable=true, options = {"comment" = "Nombre de places disponibles de la session"})
     */
    protected $nombrePlaceDisponible;

    /**
     * @var integer
     * 
     * @ORM\ManyToMany(targetEntity="\Nodevo\RoleBundle\Entity\Role")
     * @ORM\JoinTable(name="hn_module_session_rolerestriction",
     *      joinColumns={ @ORM\JoinColumn(name="ses_id", referencedColumnName="ses_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ro_id", referencedColumnName="ro_id")}
     * )
     * @ORM\OrderBy({"name" = "ASC"})
     * 
     * @GRID\Column(field="restrictionAcces.name")
     */
    protected $restrictionAcces;

    /**
     * @Assert\File(
     *     maxSize = "10M",
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "Le fichier doit être un PDF."
     * )
     */
    public $file;

    /**
     * @var string
     *
     * @ORM\Column(name="ses_path", type="string", nullable=true, length=255)
     */
    protected $path;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat", referencedColumnName="ref_id")
     * @Nodevo\Javascript(class="validate[required]")
     *
     * @GRID\Column(field="etat.libelle", options = {"comment" = "Statut pointant sur la table reference avec le code STATUT_SESSION_FORMATION de la session"})
     */
    protected $etat;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateOuvertureInscription = new \DateTime();
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

    public function getModule()
    {
        return $this->module;
    }
    
    public function getModuleId()
    {
        return $this->module->getId();
    }
    
    public function setModule( Module $module )
    {
        $this->module = $module;
    }
    
    /**
     * Set dateSession
     *
     * @param \DateTime $dateSession
     * @return Session
     */
    public function setDateSession($dateSession)
    {
        $this->dateSession = $dateSession;

        return $this;
    }

    /**
     * Get dateSession
     *
     * @return \DateTime 
     */
    public function getDateSession()
    {
        return $this->dateSession;
    }

    /**
     * Set dateOuvertureInscription
     *
     * @param \DateTime $dateOuvertureInscription
     * @return Session
     */
    public function setDateOuvertureInscription($dateOuvertureInscription)
    {
        $this->dateOuvertureInscription = $dateOuvertureInscription;

        return $this;
    }

    /**
     * Get dateOuvertureInscription
     *
     * @return \DateTime 
     */
    public function getDateOuvertureInscription()
    {
        return $this->dateOuvertureInscription;
    }

    /**
     * Set dateFermetureInscription
     *
     * @param \DateTime $dateFermetureInscription
     * @return Session
     */
    public function setDateFermetureInscription($dateFermetureInscription)
    {
        $this->dateFermetureInscription = $dateFermetureInscription;

        return $this;
    }

    /**
     * Get dateFermetureInscription
     *
     * @return \DateTime 
     */
    public function getDateFermetureInscription()
    {
        return $this->dateFermetureInscription;
    }

    /**
     * Get duree
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $duree
     */
    public function getDuree()
    {
        return $this->duree;
    }
    
    /**
     * Set duree
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $duree
     */
    public function setDuree($duree)
    {
        if($duree instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->duree = $duree;
        else
            $this->duree = null;
    }

    /**
     * Set horaires
     *
     * @param string $horaires
     * @return Session
     */
    public function setHoraires($horaires)
    {
        $this->horaires = $horaires;

        return $this;
    }

    /**
     * Get horaires
     *
     * @return string 
     */
    public function getHoraires()
    {
        return $this->horaires;
    }

    /**
     * Set lieu
     *
     * @param string $lieu
     * @return Session
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * Get lieu
     *
     * @return string 
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Session
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
     * Set nombrePlaceDisponible
     *
     * @param integer $nombrePlaceDisponible
     * @return Session
     */
    public function setNombrePlaceDisponible($nombrePlaceDisponible)
    {
        $this->nombrePlaceDisponible = $nombrePlaceDisponible;

        return $this;
    }

    /**
     * Get nombrePlaceDisponible
     *
     * @return integer 
     */
    public function getNombrePlaceDisponible()
    {
        return $this->nombrePlaceDisponible;
    }

    /**
     * Add restrictionAcces
     *
     * @param \Nodevo\RoleBundle\Entity\Role $role
     * @return Objet
     */
    public function addRestrictionAcces(\Nodevo\RoleBundle\Entity\Role $role)
    {
        $this->restrictionAcces[] = $role;
    
        return $this;
    }
    
    /**
     * Remove restrictionAcces
     *
     * @param \Nodevo\RoleBundle\Entity\Role $role
     */
    public function removeRestrictionAcces(\Nodevo\RoleBundle\Entity\Role $role)
    {
        $this->restrictionAcces->removeElement($role);
    }
    
    /**
     * Set restrictionsAcces
     *
     * @param \Doctrine\Common\Collections\Collection $restrictionsAcces
     * @return Objet
     */
    public function setRestrictionAcces($restrictionsAcces)
    {
        $this->restrictionAcces = $restrictionsAcces;
    
        return $this;
    }
    
    /**
     * Get restrictionsAcces
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRestrictionAcces()
    {
        return $this->restrictionAcces;
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

    // ----------------------------------------
    // --- Gestion de l'upload des fichiers ---
    // ----------------------------------------

    /**
     * Set path
     *
     * @param string $path
     * @return Session
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
        return __ROOT_DIRECTORY__.'/'.$this->getUploadDir();
    }
    
    public function getUploadDir()
    {
        return 'web/medias/Modules/Sessions';
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
    
            $this->path = round(microtime(true) * 1000) . '_' . $this->file->getClientOriginalName();
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
     * Set les valeurs du module lié, si il y en a un
     * 
     * @return this
     */
    public function getDefaultValueFromModule()
    {
        //Si il y a bien un module renseigné
        if(!is_null($this->getModule()))
        {
            $module = $this->getModule();
            
            //Note GME : merci php 5.3 pour les variables temporaires
            //Durée
            $duree = $module->getDuree();
            if(!is_null($duree))
                $this->setDuree($module->getDuree());
            //Horaires
            $horaire = $module->getHorairesType();
            if(!empty($horaire))
                $this->setHoraires($module->getHorairesType());
            //Lieu
            $lieu = $module->getLieu();
            if(!empty($lieu))
                $this->setLieu($module->getLieu());
            //Description
            $description = $module->getDescription();
            if(!empty($description))
                $this->setDescription($module->getDescription());
            //Nombre de places disponibles
            $nbPlaceDispo = $module->getNombrePlaceDisponible();
            if(!empty($nbPlaceDispo))
                $this->setNombrePlaceDisponible($module->getNombrePlaceDisponible());
        }
        
        return $this;
    }
}
