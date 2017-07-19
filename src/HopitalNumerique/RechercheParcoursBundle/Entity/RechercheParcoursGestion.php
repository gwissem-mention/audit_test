<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RechercheParcoursGestion.
 *
 * @ORM\Table(name="hn_recherche_recherche_parcours_gestion")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursGestionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class RechercheParcoursGestion
{
    /**
     * @var int
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
     * @var int
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
     * @var int
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
     * @var array|string[]
     *
     * @ORM\OneToMany(targetEntity="GuidedSearchConfigPublicationType", mappedBy="guidedSearchConfig", cascade={"remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $publicationsType;

    /**
     * @ORM\OneToMany(targetEntity="RechercheParcours", mappedBy="recherchesParcoursGestion")
     */
    protected $rechercheParcours;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->domaines = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referencesParentes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referencesVentilations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->publicationsType = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return RechercheParcoursGestion
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
     * Add domaines.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     *
     * @return RechercheParcoursGestion
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines[] = $domaines;

        return $this;
    }

    /**
     * Set domaines.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines
     *
     * @return RechercheParcoursGestion
     */
    public function setDomaines($domaines)
    {
        $this->domaines = $domaines;

        return $this;
    }

    /**
     * Remove domaines.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines->removeElement($domaines);
    }

    /**
     * Get domaines.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Get les ids des domaines concerné par l'user.
     *
     * @return array[integer]
     */
    public function getDomainesId()
    {
        $domainesId = [];

        foreach ($this->domaines as $domaine) {
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
     * Add referencesParentes.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $referencesParentes
     *
     * @return RechercheParcoursGestion
     */
    public function addReferencesParente(\HopitalNumerique\ReferenceBundle\Entity\Reference $referencesParentes)
    {
        $this->referencesParentes[] = $referencesParentes;

        return $this;
    }

    /**
     * Remove referencesParentes.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $referencesParentes
     */
    public function removeReferencesParente(\HopitalNumerique\ReferenceBundle\Entity\Reference $referencesParentes)
    {
        $this->referencesParentes->removeElement($referencesParentes);
    }

    /**
     * Get referencesParentes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencesParentes()
    {
        return $this->referencesParentes;
    }

    /**
     * @param Reference[] $referencesParentes
     *
     * @return RechercheParcoursGestion
     */
    public function setReferencesParentes($referencesParentes)
    {
        $this->referencesParentes = $referencesParentes;

        return $this;
    }

    /**
     * Add referencesVentilations.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $referencesVentilations
     *
     * @return RechercheParcoursGestion
     */
    public function addReferencesVentilation(\HopitalNumerique\ReferenceBundle\Entity\Reference $referencesVentilations)
    {
        $this->referencesVentilations[] = $referencesVentilations;

        return $this;
    }

    /**
     * @param Reference[] $referencesVentilations
     *
     * @return RechercheParcoursGestion
     */
    public function setReferencesVentilations($referencesVentilations)
    {
        $this->referencesVentilations = $referencesVentilations;

        return $this;
    }

    /**
     * Remove referencesVentilations.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $referencesVentilations
     */
    public function removeReferencesVentilation(\HopitalNumerique\ReferenceBundle\Entity\Reference $referencesVentilations)
    {
        $this->referencesVentilations->removeElement($referencesVentilations);
    }

    /**
     * Get referencesVentilations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferencesVentilations()
    {
        return $this->referencesVentilations;
    }

    /**
     * Get rechercheParcours.
     *
     * @return \Doctrine\Common\Collections\Collection|ArrayCollection|RechercheParcours[]
     */
    public function getRechercheParcours()
    {
        return $this->rechercheParcours;
    }

    /**
     * @return ArrayCollection|GuidedSearchConfigPublicationType[]
     */
    public function getPublicationsType()
    {
        return $this->publicationsType;
    }

    /**
     * @return ArrayCollection|GuidedSearchConfigPublicationType[]
     */
    public function getActivePublicationsType()
    {
        $result = new ArrayCollection();
        foreach ($this->getPublicationsType() as $publicationType) {
            if ($publicationType->isActive()) {
                $result->add($publicationType);
            }
        }

        return $result;
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

    /**
     * @param $publicationType
     *
     * @return bool
     */
    public function hasPublicationType($publicationTypeSlug)
    {
        foreach ($this->getPublicationsType() as $publicationType) {
            if ($publicationType->getType() === $publicationTypeSlug) {
                return $publicationType->isActive();
            }
        }

        return false;
    }
}
