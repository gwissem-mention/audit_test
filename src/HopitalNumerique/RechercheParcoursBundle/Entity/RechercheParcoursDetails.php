<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RechercheParcoursDetails
 *
 * @ORM\Table(name="hn_recherche_recherche_parcours_details")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursDetailsRepository")
 */
class RechercheParcoursDetails
{
    /**
     * @var integer
     *
     * @ORM\Column(name="rrpd_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="RechercheParcours", inversedBy="recherchesParcoursDetails")
     * @ORM\JoinColumn(name="rrp_id", referencedColumnName="rrp_id")
     */
    protected $rechercheParcours;
    
    /**
     * @var integer
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
     * @var integer
     *
     * @ORM\Column(name="rrpd_order", type="smallint", options = {"comment" = "Ordre du détails de la recherche par parcours."})
     */
    protected $order;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getRechercheParcours()
    {
        return $this->rechercheParcours;
    }
    
    public function setRechercheParcours( RechercheParcours $rechercheParcours )
    {
        $this->rechercheParcours = $rechercheParcours;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return RechercheParcoursDetails
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
     * Get reference
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     */
    public function getReference()
    {
        return $this->reference;
    }
    
    /**
     * Set reference
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     */
    public function setReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $reference)
    {
        $this->reference = $reference;
    }

    /**
     * Get order
     *
     * @return integer $order
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * Set order
     *
     * @param integer $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }
}