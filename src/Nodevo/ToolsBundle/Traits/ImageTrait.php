<?php
namespace Nodevo\ToolsBundle\Traits;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Nodevo\ToolsBundle\Tools\Fichier;

/**
 * Trait pour les entités ayant une image (copie depuis LyssalStructureBundle).
 *
 * @author Rémi Leclerc
 */
trait ImageTrait
{
    /**
     * @var boolean Si l'image a été chargée
     */
    protected $imageFileHasBeenUploaded = false;


    /**
     * Répertoire dans lequel est enregistré l'image
     * 
     * @return string Dossier de l'image
     */
    abstract public function getImageUploadDir();
    
    /**
     * Get Image
     * 
     * @return string Image
     */
    public function getImage()
    {
        return $this->image;
    }
    
    /**
     * Set Image
     * 
     * @param string $image
     * @return \Lyssal\StructureBundle\Entity\ImageTrait
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Retourne si l'entité possède l'image.
     * 
     * @return boolean VRAI si image existant
     */
    public function hasImage()
    {
        return (null !== $this->image);
    }
    
    /**
     * Get ImageFile
     * 
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile ImageFile
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set ImageFile
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $imageFile
     * @return \Lyssal\StructureBundle\Entity\ImageTrait
     */
    public function setImageFile(UploadedFile $imageFile = null)
    {
        $this->imageFile = $imageFile;

        if (null !== $this->imageFile && $this->imageFileIsValid()) {
            $this->uploadImage();
        }

        return $this;
    }

    /**
     * Retourne si l'image chargée par l'utilisateur est valide.
     *
     * @return boolean Si valide
     */
    public function imageFileIsValid()
    {
        return (null !== $this->imageFile);
    }

    /**
     * Retourne si l'image a été chargée.
     *
     * @return boolean Si chargée
     */
    public function imageFileHasBeenUploaded()
    {
        return $this->imageFileHasBeenUploaded;
    }

    /**
     * Retourne le chemin de l'image.
     *
     * @deprecated Use getImagePathname
     * @return string Chemin de l'image
     */
    public function getImageChemin()
    {
        return $this->getImagePathname();
    }
    
    /**
     * Retourne le chemin (pathname) de l'image.
     *
     * @return string Chemin de l'image
     */
    public function getImagePathname()
    {
        return $this->getImageUploadDir().DIRECTORY_SEPARATOR.$this->image;
    }

    /**
     * Enregistre l'image sur le disque.
     * 
     * @return void
     */
    protected function uploadImage()
    {
        $this->saveImage(false);
    }

    /**
     * Enregistre l'image sur le disque.
     *
     * @return void
     */
    protected function saveImage($remplaceSiExistant = false)
    {
        $this->deleteImage();

        $fichier = new Fichier($this->imageFile->getRealPath());
        if ($fichier->move($this->getImageUploadDir().DIRECTORY_SEPARATOR.$this->imageFile->getClientOriginalName(), $remplaceSiExistant)) {
            $this->image = $fichier->getNom();
            $this->setImageFile(null);
            $this->imageFileHasBeenUploaded = true;
        }
    }
    
    /**
     * Supprime le fichier.
     */
    public function deleteImage()
    {
        if ('' != $this->image && file_exists($this->getImageChemin()))
            unlink($this->getImageChemin());
    }
}
