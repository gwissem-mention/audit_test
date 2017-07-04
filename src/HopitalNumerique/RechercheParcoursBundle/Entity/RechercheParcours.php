<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RechercheParcours.
 *
 * @ORM\Table(name="hn_recherche_recherche_parcours")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursRepository")
 */
class RechercheParcours
{
    /**
     * @var int
     *
     * @ORM\Column(name="rrp_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="RechercheParcoursGestion", cascade={"persist"}, inversedBy="rechercheParcours")
     * @ORM\JoinColumn(name="rrpg_id", referencedColumnName="rrpg_id", onDelete="CASCADE")
     */
    protected $recherchesParcoursGestion;

    /**
     * @var string
     *
     * @ORM\Column(name="rrpg_description", type="text", nullable=true)
     */
    private $description;

    /**
     * Détails liés à la recherche par parcours.
     *
     * @var /HopitalNumerique/RechercheParcoursBundle/Entity/RechercheParcoursDetails
     *
     * @ORM\OneToMany(targetEntity="RechercheParcoursDetails", mappedBy="rechercheParcours", cascade={"persist", "remove" })
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $recherchesParcoursDetails;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")
     */
    protected $reference;

    /**
     * @var int
     *
     * @ORM\Column(name="rrp_order", type="smallint", options = {"comment" = "Ordre de la recherche par parcours."})
     */
    protected $order;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->recherchesParcoursDetails = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set description.
     *
     * @param string $description
     *
     * @return RechercheParcours
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add rechercheParcoursDetails.
     *
     * @param \HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails $rechercheParcoursDetails
     *
     * @return Menu
     */
    public function addRecherchesParcoursDetail(\HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails $rechercheParcoursDetails)
    {
        $this->recherchesParcoursDetails[] = $rechercheParcoursDetails;

        return $this;
    }

    /**
     * Remove rechercheParcoursDetails.
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\RechercheParcoursDetails $rechercheParcoursDetails
     */
    public function removeRecherchesParcoursDetail(\HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails $rechercheParcoursDetails)
    {
        $this->recherchesParcoursDetails->removeElement($rechercheParcoursDetails);
    }

    /**
     * Get recherchesParcoursDetails.
     *
     * @return \Doctrine\Common\Collections\Collection|RechercheParcoursDetails[]
     */
    public function getRecherchesParcoursDetails()
    {
        return $this->recherchesParcoursDetails;
    }

    /**
     * Get recherchesParcoursDetails ids.
     *
     * @return array
     */
    public function getRecherchesParcoursDetailsIds()
    {
        $ids = [];

        foreach ($this->recherchesParcoursDetails as $etape) {
            $ids[] = $etape->getId();
        }

        return $ids;
    }

    /**
     * Get reference.
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set reference.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     *
     * @return RechercheParcours
     */
    public function setReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get order.
     *
     * @return int $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order.
     *
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set recherchesParcoursGestion.
     *
     * @param \HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion $recherchesParcoursGestion
     *
     * @return RechercheParcours
     */
    public function setRecherchesParcoursGestion(\HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion $recherchesParcoursGestion = null)
    {
        $this->recherchesParcoursGestion = $recherchesParcoursGestion;

        return $this;
    }

    /**
     * Get recherchesParcoursGestion.
     *
     * @return \HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion
     */
    public function getRecherchesParcoursGestion()
    {
        return $this->recherchesParcoursGestion;
    }
}
