<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefObjet
 *
 * @ORM\Table("hn_objet_contenu_reference")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\RefContenuRepository")
 */
class RefContenu
{
    /**
     * @var integer
     *
     * @ORM\Column(name="refc_id", type="integer", options = {"comment" = "ID de la référence du contenu"})
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
     * @ORM\ManyToOne(targetEntity="Contenu", inversedBy="references")
     * @ORM\JoinColumn(name="con_id", referencedColumnName="con_id", onDelete="CASCADE")
     */
    private $contenu;

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
     * Get contenu
     *
     * @return \HopitalNumerique\ObjetBundle\Entity\Contenu $contenu
     */
    public function getContenu()
    {
        return $this->contenu;
    }
    
    /**
     * Set contenu
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Contenu $contenu
     */
    public function setContenu(\HopitalNumerique\ObjetBundle\Entity\Contenu $contenu)
    {
        $this->contenu = $contenu;
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