<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * RechercheParcoursDetails.
 *
 * @ORM\Table(name="hn_recherche_recherche_parcours_details")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursDetailsRepository")
 */
class RechercheParcoursDetails
{
    /**
     * @var int
     *
     * @ORM\Column(name="rrpd_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var RechercheParcours
     *
     * @ORM\ManyToOne(targetEntity="RechercheParcours", cascade={"persist"}, inversedBy="recherchesParcoursDetails")
     * @ORM\JoinColumn(name="rrp_id", referencedColumnName="rrp_id", onDelete="CASCADE")
     */
    protected $rechercheParcours;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")
     */
    protected $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="mod_description", type="text", nullable=true, options = {"comment" = "Description du détail de la recherche par parcours."})
     */
    protected $description;

    /**
     * @var int
     *
     * @ORM\Column(name="rrpd_order", type="smallint", options = {"comment" = "Ordre du détails de la recherche par parcours."})
     */
    protected $order;

    /**
     * @var bool
     *
     * @ORM\Column(name="rrpd_afficher_enfant", type="boolean", options = {"comment" = "Afficher les enfants de la reference en front"})
     */
    protected $showChildren;

    /**
     * Constructeur.
     */
    public function __construct()
    {
        $this->showChildren = false;
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
     * @return RechercheParcours
     */
    public function getRechercheParcours()
    {
        return $this->rechercheParcours;
    }

    /**
     * @param RechercheParcours $rechercheParcours
     */
    public function setRechercheParcours(RechercheParcours $rechercheParcours)
    {
        $this->rechercheParcours = $rechercheParcours;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return RechercheParcoursDetails
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
     * Get reference.
     *
     * @return Reference $reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set reference.
     *
     * @param Reference $reference
     */
    public function setReference(Reference $reference)
    {
        $this->reference = $reference;
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
     *
     * @return RechercheParcoursDetails
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set showChildren.
     *
     * @param bool $showChildren
     *
     * @return RechercheParcoursDetails
     */
    public function setShowChildren($showChildren)
    {
        $this->showChildren = $showChildren;

        return $this;
    }

    /**
     * Get showChildren.
     *
     * @return bool
     */
    public function getShowChildren()
    {
        return $this->showChildren;
    }
}
