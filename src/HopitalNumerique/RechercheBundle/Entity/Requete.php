<?php

namespace HopitalNumerique\RechercheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Requete
 *
 * @ORM\Table(name="hn_requete")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheBundle\Repository\RequeteRepository")
 */
class Requete
{
    /**
     * @var integer
     *
     * @ORM\Column(name="req_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="req_nom", type="string", length=128, options = {"comment" = "Nom de la requete"})
     */
    private $nom;

    /**
     * @var boolean
     *
     * @ORM\Column(name="req_isDefault", type="boolean", options = {"comment" = "La requete est-elle celle par default ?"})
     */
    private $isDefault;

    /**
     * @var boolean
     *
     * @ORM\Column(name="req_isNew", type="boolean", options = {"comment" = "Un element de la requete est taggue comme nouveau ?"})
     */
    private $isNew;

    /**
     * @var boolean
     *
     * @ORM\Column(name="req_isUpdated", type="boolean", options = {"comment" = "Un element de la requete est taggue comme mis a jour ?"})
     */
    private $isUpdated;

    /**
     * @var boolean
     *
     * @ORM\Column(name="req_isUserNotified", type="boolean", options = {"comment" = "L utilisateur a demande d etre notifie sur cette requete"})
     */
    private $isUserNotified;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="obj_date_debut", type="datetime", options = {"comment" = "Date de debut de la notification"}, nullable=true)
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="obj_date_fin", type="datetime", options = {"comment" = "Date de fin de la notification"}, nullable=true)
     */
    private $dateFin;

    /**
     * @var array
     *
     * @ORM\Column(name="req_refs", type="json_array")
     */
    private $refs;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")
     */
    private $user;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->isDefault      = false;
        $this->isNew          = false;
        $this->isUpdated      = false;
        $this->isUserNotified = true;
        $this->dateDebut      = null;
        $this->dateFin        = null;
        $this->refs           = array();
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
     * Set nom
     *
     * @param string $nom
     * @return Requete
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return Requete
     */
    public function setDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean 
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * Get isNew
     *
     * @return boolean $isNew
     */
    public function isNew()
    {
        return $this->isNew;
    }
    
    /**
     * Set isNew
     *
     * @param boolean $isNew
     */
    public function setNew($isNew)
    {
        $this->isNew = $isNew;
    }
    
    /**
     * Get isUpdated
     *
     * @return boolean $isUpdated
     */
    public function isUpdated()
    {
        return $this->isUpdated;
    }
    
    /**
     * Set isUpdated
     *
     * @param boolean $isUpdated
     */
    public function setUpdated($isUpdated)
    {
        $this->isUpdated = $isUpdated;
    }
    
    /**
     * Get isUserNotified
     *
     * @return boolean $isUserNotified
     */
    public function isUserNotified()
    {
        return $this->isUserNotified;
    }
    
    /**
     * Set isUserNotified
     *
     * @param boolean $isUserNotified
     */
    public function setUserNotified($isUserNotified)
    {
        $this->isUserNotified = $isUserNotified;
    }
    
    /**
     * Get dateDebut
     *
     * @return DateTime $dateDebut
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }
    
    /**
     * Set dateDebut
     *
     * @param DateTime $dateDebut
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;
    }
    
    /**
     * Get dateFin
     *
     * @return DateTime $dateFin
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }
    
    /**
     * Set dateFin
     *
     * @param DateTime $dateFin
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;
    }
    
    /**
     * Set refs
     *
     * @param array $refs
     * @return Requete
     */
    public function setRefs($refs)
    {
        $this->refs = $refs;

        return $this;
    }

    /**
     * Get refs
     *
     * @return array 
     */
    public function getRefs()
    {
        return $this->refs;
    }

    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User $user
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }
}