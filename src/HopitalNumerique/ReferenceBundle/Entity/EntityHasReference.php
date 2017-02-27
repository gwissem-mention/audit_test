<?php

namespace HopitalNumerique\ReferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityHasReference.
 *
 * @ORM\Table(name="hn_entity_has_reference",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="ENTITY_REFERENCE", columns={"entref_entity_id", "entref_entity_type", "ref_id"})
 *     },
 *     indexes={
 *         @ORM\Index(name="ENTITY_ID_INDEX", columns={"entref_entity_id"}),
 *         @ORM\Index(name="ENTITY_TYPE_INDEX", columns={"entref_entity_type"}),
 *         @ORM\Index(name="ENTITY_ID_TYPE_INDEX", columns={"entref_entity_type", "entref_entity_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository")
 */
class EntityHasReference
{
    /**
     * @var int
     *
     * @ORM\Column(name="entref_id", type="integer", options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="entref_entity_id", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $entityId;

    /**
     * @var int
     *
     * @ORM\Column(name="entref_entity_type", type="smallint", nullable=false)
     */
    private $entityType;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="Reference")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", nullable=false, onDelete="CASCADE")
     */
    private $reference;

    /**
     * @var bool
     *
     * @ORM\Column(name="entref_primary", type="boolean", nullable=false, options={"default"=false})
     */
    private $primary;

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
     * Set entityId.
     *
     * @param int $entityId
     *
     * @return EntityHasReference
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set entityType.
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
     * Get entityType.
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Set reference.
     *
     * @param Reference $reference
     *
     * @return EntityHasReference
     */
    public function setReference(Reference $reference = null)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference.
     *
     * @return Reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set primary.
     *
     * @param bool $primary
     *
     * @return EntityHasReference
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;

        return $this;
    }

    /**
     * Get primary.
     *
     * @return bool
     */
    public function isPrimary()
    {
        return $this->primary;
    }
}
