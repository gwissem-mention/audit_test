<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * RechercheParcoursGestion
 *
 * @ORM\Table(name="hn_recherche_recherche_parcours_gestion")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursGestionRepository")
 * @ORM\HasLifecycleCallbacks()
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
     * @Assert\Count(min=1);
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
        $this->referencesParentes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referencesVentilations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->typePublication = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set domaines
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines
     * @return RechercheParcoursGestion
     */
    public function setDomaines($domaines)
    {
        $this->domaines = $domaines;

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
     * Retourne si l'entité est liée au domaine.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     */
    public function hasDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        foreach ($this->domaines as $domaineExistant) {
            if ($domaineExistant->equals($domaine)) {
                return true;
            }
        }

        return false;
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

    /**
     * Recupération du type de publication pour un filtre sur les objets
     *
     * @return [type]
     */
    public function getPublicationString()
    {
        $name = array();

        foreach ($this->typePublication as $typePublication)
        {
            switch ($typePublication->getId()) 
            {
                case 175:
                    $name[] = "production";
                    break;
                case 183:
                    $name[] = "ressource";
                    break;
                case 184:
                    $name[] = "point-dur";
                    break;
                default:
                    $name[] = "null";
                    break;
            }
        }


        return $name;
    }


    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->refreshReferencesParentes();
        $this->refreshReferencesVentilations();
    }

    /**
     * Retourne si le parcours possède le type Point dur.
     *
     * @return boolean Si possède
     */
    public function hasTypePublicationPointDur()
    {
        return $this->hasTypePublicationId(Reference::CATEGORIE_OBJET_POINT_DUR_ID);
    }

    /**
     * Retourne si le parcours possède le type Production.
     *
     * @return boolean Si possède
     */
    public function hasTypePublicationProduction()
    {
        return $this->hasTypePublicationId(Reference::CATEGORIE_OBJET_PRODUCTION_ID);
    }

    /**
     * Retourne si le parcours possède un type de publication.
     *
     * @param integer $referenceId ID du type
     * @return boolean Si possède
     */
    private function hasTypePublicationId($referenceId)
    {
        foreach ($this->typePublication as $typePublication) {
            if ($referenceId === $typePublication->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Supprime les références n'appartenant pas aux domaines de l'entité.
     */
    private function refreshReferencesParentes()
    {
        foreach ($this->referencesParentes as $referenceParent) {
            if (!$referenceParent->isAllDomaines()) {
                $referenceHasDomaine = false;
                foreach ($referenceParent->getDomaines() as $referenceDomaine) {
                    if ($this->hasDomaine($referenceDomaine)) {
                        $referenceHasDomaine = true;
                        break;
                    }
                }

                if (!$referenceHasDomaine) {
                    $this->removeReferencesParente($referenceParent);
                }
            }
        }
    }

    /**
     * Supprime les références n'appartenant pas aux domaines de l'entité.
     */
    private function refreshReferencesVentilations()
    {
        foreach ($this->referencesVentilations as $referenceParent) {
            if (!$referenceParent->isAllDomaines()) {
                $referenceHasDomaine = false;
                foreach ($referenceParent->getDomaines() as $referenceDomaine) {
                    if ($this->hasDomaine($referenceDomaine)) {
                        $referenceHasDomaine = true;
                        break;
                    }
                }

                if (!$referenceHasDomaine) {
                    $this->removeReferencesVentilation($referenceParent);
                }
            }
        }
    }
}
