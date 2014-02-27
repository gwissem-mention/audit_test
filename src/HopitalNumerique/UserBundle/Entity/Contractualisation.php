<?php

namespace HopitalNumerique\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * User
 *
 * @ORM\Table("core_user_contractualisation")
 * @ORM\Entity(repositoryClass="HopitalNumerique\UserBundle\Repository\ContractualisationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Contractualisation
{
    /**
     * @ORM\Column(name="ctn_id", type="integer", options = {"comment" = "ID de la contractualisation"})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;  
        
    /**
     * @Assert\File(
     *     maxSize = "10M",
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "Le fichier doit être un PDF."
     * )
     * @Nodevo\Javascript(class="validate[required]")
     */
    public $file;
    
    /**
     * @var string
     *
     * @ORM\Column(name="ctn_path", type="string", length=255, nullable=true, options = {"comment" = "Nom du document pdf stocké"})
     */
    protected $path;
    
    /**
     * @var string
     * @Assert\NotBlank(message="Le nom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "3",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[3],maxSize[255]]")
     * @ORM\Column(name="ctn_nom_document", type="string", length=255, options = {"comment" = "Nom du document pdf utilisateur"})
     */
    protected $nomDocument;
    
    /**
     * @var boolean
     * 
     * @ORM\Column(name="ctn_archiver", type="boolean", nullable=true, options = {"comment" = "Document archivé ?"})
     */
    protected $archiver;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ctn_date_renouvellement", type="datetime", nullable=true, options = {"comment" = "Date de renouvellement"})
     */
    protected $dateRenouvellement;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_type_document", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="Le type de document ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $typeDocument;    

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="contractualisations")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")
     */
    private $user;    

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
     * Set nomDocument
     *
     * @param string $nomDocument
     * @return Contractualisation
     */
    public function setNomDocument($nomDocument)
    {
        $this->nomDocument = $nomDocument;

        return $this;
    }

    /**
     * Get nomDocument
     *
     * @return string 
     */
    public function getNomDocument()
    {
        return $this->nomDocument;
    }

    /**
     * Set dateRenouvellement
     *
     * @param \DateTime $dateRenouvellement
     * @return Contractualisation
     */
    public function setDateRenouvellement($dateRenouvellement)
    {
        $this->dateRenouvellement = $dateRenouvellement;

        return $this;
    }

    /**
     * Get dateRenouvellement
     *
     * @return \DateTime 
     */
    public function getDateRenouvellement()
    {
        return $this->dateRenouvellement;
    }

    /**
     * Set typeDocument
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $typeDocument
     * @return Contractualisation
     */
    public function setTypeDocument(\HopitalNumerique\ReferenceBundle\Entity\Reference $typeDocument = null)
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    /**
     * Get typeDocument
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getTypeDocument()
    {
        return $this->typeDocument;
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
        return 'files/contractualisation';
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
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return Contractualisation
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
