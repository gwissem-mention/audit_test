<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * RechercheParcoursGestion
 *
 * @ORM\Table(name="hn_recherche_recherche_parcours_gestion")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursGestionRepository")
 */
class RechercheParcoursGestion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="rrpg_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="rrpg_nom", type="string", length=255)
     */
    protected $nom;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(name="hn_domaine_gestions_parcours_guide",
     *      joinColumns={ @ORM\JoinColumn(name="rrpg_id", referencedColumnName="rrpg_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    /**
     * @var integer
     * 
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_recherche_recherche_parcours_gestion_reference_parente",
     *      joinColumns={ @ORM\JoinColumn(name="rrpg_id", referencedColumnName="rrpg_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")}
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $referencesParentes;

    /**
     * @var integer
     * 
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_recherche_recherche_parcours_gestion_reference_ventilation",
     *      joinColumns={ @ORM\JoinColumn(name="rrpg_id", referencedColumnName="rrpg_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")}
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $referencesVentilations;

    /**
     * @var integer
     * 
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_recherche_recherche_parcours_gestion_type_publication",
     *      joinColumns={ @ORM\JoinColumn(name="rrpg_id", referencedColumnName="rrpg_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")}
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $typePublication;

    /**
     * @ORM\OneToMany(targetEntity="RechercheParcours", mappedBy="recherchesParcoursGestion")
     */
    protected $rechercheParcours;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->domaines = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return RechercheParcoursGestion
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
     * Add domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     * @return RechercheParcoursGestion
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines[] = $domaines;

        return $this;
    }

    /**
     * Remove domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines->removeElement($domaines);
    }

    /**
     * Get domaines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Get les ids des domaines concerné par l'user
     *
     * @return array[integer]
     */
    public function getDomainesId()
    {
        $domainesId = array();

        foreach ($this->domaines as $domaine) 
        {
            $domainesId[] = $domaine->getId();
        }

        return $domainesId;
    }

    /**
     * Add referencesParentes
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $referencesParentes
     * @return RechercheParcoursGestion
     */
    public function addReferencesParente(\HopitalNumerique\ReferenceBundle\Entity\Reference $referencesParentes)
    {
        $this->referencesParentes[] = $referencesParentes;

        return $this;
    }

    /**
     * Remove referencesParentes
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $referencesParentes
     */
    public function removeReferencesParente(\HopitalNumerique\ReferenceBundle\Entity\Reference $referencesParentes)
    {
        $this->referencesParentes->removeElement($referencesParentes);
    }

    /**
     * Get referencesParentes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReferencesParentes()
    {
        return $this->referencesParentes;
    }

    /**
     * Add referencesVentilations
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $referencesVentilations
     * @return RechercheParcoursGestion
     */
    public function addReferencesVentilation(\HopitalNumerique\ReferenceBundle\Entity\Reference $referencesVentilations)
    {
        $this->referencesVentilations[] = $referencesVentilations;

        return $this;
    }

    /**
     * Remove referencesVentilations
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $referencesVentilations
     */
    public function removeReferencesVentilation(\HopitalNumerique\ReferenceBundle\Entity\Reference $referencesVentilations)
    {
        $this->referencesVentilations->removeElement($referencesVentilations);
    }

    /**
     * Get referencesVentilations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReferencesVentilations()
    {
        return $this->referencesVentilations;
    }

    /**
     * Add rechercheParcours
     *
     * @param \HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours $rechercheParcours
     * @return RechercheParcoursGestion
     */
    public function addRechercheParcour(\HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours $rechercheParcours)
    {
        $this->rechercheParcours[] = $rechercheParcours;

        return $this;
    }

    /**
     * Remove rechercheParcours
     *
     * @param \HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours $rechercheParcours
     */
    public function removeRechercheParcour(\HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours $rechercheParcours)
    {
        $this->rechercheParcours->removeElement($rechercheParcours);
    }

    /**
     * Get rechercheParcours
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRechercheParcours()
    {
        return $this->rechercheParcours;
    }

    /**
     * Add typePublication
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $typePublication
     * @return RechercheParcoursGestion
     */
    public function addTypePublication(\HopitalNumerique\ReferenceBundle\Entity\Reference $typePublication)
    {
        $this->typePublication[] = $typePublication;

        return $this;
    }

    /**
     * Remove typePublication
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $typePublication
     */
    public function removeTypePublication(\HopitalNumerique\ReferenceBundle\Entity\Reference $typePublication)
    {
        $this->typePublication->removeElement($typePublication);
    }

    /**
     * Get typePublication
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTypePublication()
    {
        return $this->typePublication;
    }
}
