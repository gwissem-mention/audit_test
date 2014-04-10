<?php

namespace HopitalNumerique\ModuleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Module
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="HopitalNumerique\ModuleBundle\Repository\ModuleRepository")
 */
class Module
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=255)
     */
    protected $titre;

    /**
     * @var string
     *
     * @ORM\Column(name="horairesType", type="string", length=255)
     */
    protected $horairesType;

    /**
     * @var string
     *
     * @ORM\Column(name="lieu", type="text")
     */
    protected $lieu;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="nombrePlaceDisponible", type="integer")
     */
    protected $nombrePlaceDisponible;

    /**
     * @var string
     *
     * @ORM\Column(name="prerequis", type="text")
     */
    protected $prerequis;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_duree", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="duree.libelle")
     */
    protected $duree;

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
     * @return Module
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
     * Set horairesType
     *
     * @param string $horairesType
     * @return Module
     */
    public function setHorairesType($horairesType)
    {
        $this->horairesType = $horairesType;

        return $this;
    }

    /**
     * Get horairesType
     *
     * @return string 
     */
    public function getHorairesType()
    {
        return $this->horairesType;
    }

    /**
     * Set lieu
     *
     * @param string $lieu
     * @return Module
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
     * @return Module
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
     * @return Module
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
     * Set prerequis
     *
     * @param string $prerequis
     * @return Module
     */
    public function setPrerequis($prerequis)
    {
        $this->prerequis = $prerequis;

        return $this;
    }

    /**
     * Get prerequis
     *
     * @return string 
     */
    public function getPrerequis()
    {
        return $this->prerequis;
    }
}
