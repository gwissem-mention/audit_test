<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefChapitre
 *
 * @ORM\Table("hn_outil_chapitre_reference")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\RefChapitreRepository")
 */
class RefChapitre
{
    /**
     * @var integer
     *
     * @ORM\Column(name="refc_id", type="integer")
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
     * @ORM\ManyToOne(targetEntity="Chapitre", inversedBy="references")
     * @ORM\JoinColumn(name="cha_id", referencedColumnName="cha_id", onDelete="CASCADE")
     */
    private $chapitre;

    /**
     * @var boolean
     *
     * @ORM\Column(name="refc_primary", type="boolean", options = {"comment" = "La référence est de type primaire ?"})
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
     * Get chapitre
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Chapitre $chapitre
     */
    public function getChapitre()
    {
        return $this->chapitre;
    }
    
    /**
     * Set chapitre
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Chapitre $chapitre
     */
    public function setChapitre(\HopitalNumerique\AutodiagBundle\Entity\Chapitre $chapitre)
    {
        $this->chapitre = $chapitre;
        return $this;
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
        return $this;
    }
    
}
