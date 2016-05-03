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
     * @var string Index des types d'entités
     */
    const CATEGORY_FILTERS_ENTITY_TYPES_KEY = '1';

    /**
     * @var string Index des catégories de publication
     */
    const CATEGORY_FILTERS_PUBLICATION_CATEGORIES_KEY = '2';


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
     * @ORM\Column(name="req_nom", type="string", length=128, options = {"comment" = "Nom de la requete"}, nullable=false)
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
     * @ORM\Column(name="req_refs", type="json_array", nullable=true)
     */
    private $refs;

    /**
     * @var string
     *
     * @ORM\Column(name="req_categ_point_dur", type="json_array", nullable=true)
     */
    private $categPointDur;

    /**
     * @var string
     *
     * @ORM\Column(name="req_recherche_textuelle", type="string", length=256, nullable=true)
     */
    private $rechercheTextuelle;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", inversedBy="requetes", cascade={"persist"})
     * @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", nullable=false)
     */
    protected $domaine;

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
        $this->categPointDur = [];
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

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return Requete
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean 
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set isNew
     *
     * @param boolean $isNew
     * @return Requete
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;

        return $this;
    }

    /**
     * Get isNew
     *
     * @return boolean 
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * Set isUpdated
     *
     * @param boolean $isUpdated
     * @return Requete
     */
    public function setIsUpdated($isUpdated)
    {
        $this->isUpdated = $isUpdated;

        return $this;
    }

    /**
     * Get isUpdated
     *
     * @return boolean 
     */
    public function getIsUpdated()
    {
        return $this->isUpdated;
    }

    /**
     * Set isUserNotified
     *
     * @param boolean $isUserNotified
     * @return Requete
     */
    public function setIsUserNotified($isUserNotified)
    {
        $this->isUserNotified = $isUserNotified;

        return $this;
    }

    /**
     * Get isUserNotified
     *
     * @return boolean 
     */
    public function getIsUserNotified()
    {
        return $this->isUserNotified;
    }

    /**
     * Set categPointDur
     *
     * @param string $categPointDur
     * @return Requete
     */
    public function setCategPointDur($categPointDur)
    {
        $this->categPointDur = $categPointDur;

        return $this;
    }

    /**
     * Get categPointDur
     *
     * @return string 
     */
    public function getCategPointDur()
    {
        return $this->categPointDur;
    }

    /**
     * Set rechercheTextuelle
     *
     * @param string $rechercheTextuelle
     * @return Requete
     */
    public function setRechercheTextuelle($rechercheTextuelle)
    {
        $this->rechercheTextuelle = $rechercheTextuelle;

        return $this;
    }

    /**
     * Get rechercheTextuelle
     *
     * @return string 
     */
    public function getRechercheTextuelle()
    {
        return $this->rechercheTextuelle;
    }

    /**
     * Set domaine
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     * @return Requete
     */
    public function setDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine = null)
    {
        $this->domaine = $domaine;

        return $this;
    }

    /**
     * Get domaine
     *
     * @return \HopitalNumerique\DomaineBundle\Entity\Domaine 
     */
    public function getDomaine()
    {
        return $this->domaine;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->nom;
    }


    /**
     * Retourne les IDs des types d'entité.
     *
     * @return array<integer>|null IDs
     */
    public function getEntityTypeIds()
    {
        $categoryFilters = $this->getCategPointDur();

        if (array_key_exists(Requete::CATEGORY_FILTERS_ENTITY_TYPES_KEY, $categoryFilters)) {
            return $categoryFilters[Requete::CATEGORY_FILTERS_ENTITY_TYPES_KEY];
        }

        return null;
    }

    /**
     * Retourne les IDs des catégories de publication.
     *
     * @return array<integer>|null IDs
     */
    public function getPublicationCategoryIds()
    {
        $categoryFilters = $this->getCategPointDur();

        if (array_key_exists(Requete::CATEGORY_FILTERS_PUBLICATION_CATEGORIES_KEY, $categoryFilters)) {
            return $categoryFilters[Requete::CATEGORY_FILTERS_PUBLICATION_CATEGORIES_KEY];
        }

        return null;
    }
}
