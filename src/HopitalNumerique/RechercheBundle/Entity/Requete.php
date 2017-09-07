<?php

namespace HopitalNumerique\RechercheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

/**
 * Requete.
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
     * @var int
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
     * @var bool
     *
     * @ORM\Column(name="req_isDefault", type="boolean", options = {"comment" = "La requete est-elle celle par default ?"})
     */
    private $isDefault;

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
     * Initialisation de l'entitée (valeurs par défaut).
     */
    public function __construct()
    {
        $this->isDefault = false;
        $this->dateDebut = null;
        $this->dateFin = null;
        $this->refs = [];
        $this->categPointDur = [];
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom.
     *
     * @param string $nom
     *
     * @return Requete
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set isDefault.
     *
     * @param bool $isDefault
     *
     * @return Requete
     */
    public function setDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault.
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * Get dateDebut.
     *
     * @return \DateTime $dateDebut
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateDebut.
     *
     * @param \DateTime $dateDebut
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;
    }

    /**
     * Get dateFin.
     *
     * @return \DateTime $dateFin
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set dateFin.
     *
     * @param \DateTime $dateFin
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;
    }

    /**
     * Set refs.
     *
     * @param array $refs
     *
     * @return Requete
     */
    public function setRefs($refs)
    {
        $this->refs = $refs;

        return $this;
    }

    /**
     * Get refs.
     *
     * @return array
     */
    public function getRefs()
    {
        return $this->refs;
    }

    /**
     * Get user.
     *
     * @return User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Set isDefault.
     *
     * @param bool $isDefault
     *
     * @return Requete
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault.
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set categPointDur.
     *
     * @param string $categPointDur
     *
     * @return Requete
     */
    public function setCategPointDur($categPointDur)
    {
        $this->categPointDur = $categPointDur;

        return $this;
    }

    /**
     * Get categPointDur.
     *
     * @return array
     */
    public function getCategPointDur()
    {
        return $this->categPointDur;
    }

    /**
     * Set rechercheTextuelle.
     *
     * @param string $rechercheTextuelle
     *
     * @return Requete
     */
    public function setRechercheTextuelle($rechercheTextuelle)
    {
        $this->rechercheTextuelle = $rechercheTextuelle;

        return $this;
    }

    /**
     * Get rechercheTextuelle.
     *
     * @return string
     */
    public function getRechercheTextuelle()
    {
        return $this->rechercheTextuelle;
    }

    /**
     * Set domaine.
     *
     * @param Domaine $domaine
     *
     * @return Requete
     */
    public function setDomaine(Domaine $domaine = null)
    {
        $this->domaine = $domaine;

        return $this;
    }

    /**
     * Get domaine.
     *
     * @return Domaine
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

        if (array_key_exists(self::CATEGORY_FILTERS_ENTITY_TYPES_KEY, $categoryFilters)) {
            return $categoryFilters[self::CATEGORY_FILTERS_ENTITY_TYPES_KEY];
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

        if (array_key_exists(self::CATEGORY_FILTERS_PUBLICATION_CATEGORIES_KEY, $categoryFilters)) {
            return $categoryFilters[self::CATEGORY_FILTERS_PUBLICATION_CATEGORIES_KEY];
        }

        return null;
    }
}
