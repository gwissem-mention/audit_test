<?php

namespace HopitalNumerique\RechercheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefExpBesoinReponses
 *
 * @ORM\Table("hn_recherche_expBesoinReponses_reference")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheBundle\Repository\RefExpBesoinReponsesRepository")
 */
class RefExpBesoinReponses
{
    /**
     * @var integer
     *
     * @ORM\Column(name="refexpbr_id", type="integer", options = {"comment" = "ID de la référence du expBesoinReponses"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")
     */
    private $reference;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses")
     * @ORM\JoinColumn(name="expbr_id", referencedColumnName="expbr_id", nullable=true, onDelete="CASCADE")
     */
    protected $expBesoinReponses;

    /**
     * @var boolean
     *
     * @ORM\Column(name="reftop_primary", type="boolean", options = {"comment" = "La référence est de type primaire ?"})
     */
    private $primary;

    public function __construct()
    {
        $this->primary = true;
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
     * Get expBesoinReponses
     *
     * @return ExpBesoinReponses $expBesoinReponses
     */
    public function getExpBesoinReponses()
    {
        return $this->expBesoinReponses;
    }
    
    /**
     * Set expBesoinReponses
     *
     * @param ExpBesoinReponses $expBesoinReponses
     */
    public function setExpBesoinReponses(\HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $expBesoinReponses)
    {
        $this->expBesoinReponses = $expBesoinReponses;
    }
    
    /**
     * Get primary
     *
     * @return boolean $primary
     */
    public function getPrimary()
    {
        return $this->primary;
    }
    
    /**
     * Set primary
     *
     * @param boolean $primary
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;
    }
}