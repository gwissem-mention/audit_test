<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * FichierModifiable
 *
 * @ORM\Table(name="hn_objet_fichiermodifiable")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\FichierModifiableRepository")
 * @ORM\HasLifecycleCallbacks
 */
class FichierModifiable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ofm_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ofm_referentAnap", type="string", length=255, nullable=true)
     */
    protected $referentAnap;

    /**
     * @var string
     *
     * @ORM\Column(name="ofm_sourceDocument", type="string", length=255, nullable=true)
     */
    protected $sourceDocument;

    /**
     * @var string
     *
     * @ORM\Column(name="ofm_commentaires", type="text", nullable=true)
     */
    protected $commentaires;

    /**
     * @var string
     *
     * @ORM\Column(name="ofm__path_edit", type="string", length=255, nullable=true, options = {"comment" = "Nom du fichier éditable"})
     */
    private $pathEdit;

    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="Objet", inversedBy="fichierModifiable")
     * @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id", onDelete="CASCADE")
     */
    protected $objet;

    /**
     * @Assert\File(
     *     maxSize = "10M"
     * )
     */
    public $fileEdit;

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
     * Set referentAnap
     *
     * @param string $referentAnap
     * @return FichierModifiable
     */
    public function setReferentAnap($referentAnap)
    {
        $this->referentAnap = $referentAnap;

        return $this;
    }

    /**
     * Get referentAnap
     *
     * @return string 
     */
    public function getReferentAnap()
    {
        return $this->referentAnap;
    }

    /**
     * Set sourceDocument
     *
     * @param string $sourceDocument
     * @return FichierModifiable
     */
    public function setSourceDocument($sourceDocument)
    {
        $this->sourceDocument = $sourceDocument;

        return $this;
    }

    /**
     * Get sourceDocument
     *
     * @return string 
     */
    public function getSourceDocument()
    {
        return $this->sourceDocument;
    }

    /**
     * Set commentaires
     *
     * @param string $commentaires
     * @return FichierModifiable
     */
    public function setCommentaires($commentaires)
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    /**
     * Get commentaires
     *
     * @return string 
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }

    /**
     * Set objet
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $objet
     * @return FichierModifiable
     */
    public function setObjet(\HopitalNumerique\ObjetBundle\Entity\Objet $objet = null)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet
     *
     * @return \HopitalNumerique\ObjetBundle\Entity\Objet 
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set pathEdit
     *
     * @param string $pathEdit
     * @return Objet
     */
    public function setPathEdit($pathEdit)
    {
        if( is_null($pathEdit) && file_exists($this->getAbsolutePath()) )
            unlink($this->getAbsolutePath());

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
     * [getAbsolutePath description]
     *
     * @param  [type] $type [description]
     *
     * @return [type]
     */
    public function getAbsolutePath()
    {
        $result = null;

        if( !is_null($this->pathEdit) )
            $result = $this->pathEdit;

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
    public function getWebPath()
    {
        $result = null;

        
        if( !is_null($this->pathEdit) )
            $result = $this->pathEdit;

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

        $result = $this->pathEdit;

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
        if (null !== $this->fileEdit){
            //delete Old File
            if ( file_exists($this->getAbsolutePath()) )
                unlink($this->getAbsolutePath());

            //$this->pathEdit = $this->fileEdit->getClientOriginalName();
            $this->pathEdit = $this->getObjet()->getId() . '_' . $this->fileEdit->getClientOriginalName();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if ( null === $this->fileEdit )
            return;
        
        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si erreur il y a   

        if ( null !== $this->fileEdit )
        {
            $this->fileEdit->move($this->getUploadRootDir(), $this->pathEdit);
            unset($this->fileEdit);
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ( $fileEdit = $this->getAbsolutePath() && file_exists( $this->getAbsolutePath() ) )
            unlink($fileEdit);
    }
}
