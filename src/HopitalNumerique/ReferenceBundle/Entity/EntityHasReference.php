<?php
namespace HopitalNumerique\ReferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityHasReference.
 *
 * @ORM\Table(name="hn_entity_has_reference", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ENTITY_REFERENCE", columns={"entref_entity_id", "entref_entity_type", "ref_id"})
 * })
 * @ORM\Entity(repositoryClass="HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository")
 */
class EntityHasReference
{
    /**
     * @var integer
     *
     * @ORM\Column(name="entref_id", type="integer", options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entref_entity_id", type="integer", options={"unsigned"=true})
     */
    private $entityId;

    /**
     * @var integer
     *
     * @ORM\Column(name="entref_entity_type", type="smallint", nullable=false)
     */
    private $entityType;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference
     *
     * @ORM\ManyToOne(targetEntity="Reference")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")
     */
    private $reference;

    /**
     * @var boolean
     *
     * @ORM\Column(name="entref_primary", type="boolean", nullable=false, options={"default"=false})
     */
    private $primary;


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
     * Set entityId
     *
     * @param integer $entityId
     *
     * @return EntityHasReference
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return integer
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set entityType
     *
     * @param string $entityType
     *
     * @return EntityHasReference
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * Get entityType
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Set reference
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     *
     * @return EntityHasReference
     */
    public function setReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $reference = null)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set primary
     *
     * @param boolean $primary
     *
     * @return EntityHasReference
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;

        return $this;
    }

    /**
     * Get primary
     *
     * @return boolean
     */
    public function isPrimary()
    {
        return $this->primary;
    }
}
