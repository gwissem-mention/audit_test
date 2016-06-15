<?php
namespace HopitalNumerique\ReferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityHasGlossaire.
 *
 * @ORM\Table(name="hn_entity_has_glossaire", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ENTITY_DOMAINE", columns={"entglo_entity_id", "entglo_entity_type", "dom_id"})
 * })
 * @ORM\Entity()
 */
class EntityHasGlossaire
{
    /**
     * @var integer
     *
     * @ORM\Column(name="entglo_id", type="integer", options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entglo_entity_type", type="smallint", nullable=false)
     */
    private $entityType;

    /**
     * @var integer
     *
     * @ORM\Column(name="entglo_entity_id", type="integer", options={"unsigned"=true}, nullable=false)
     */
    private $entityId;

    /**
     * @var \HopitalNumerique\DomaineBundle\Entity\Domaine
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", nullable=false, onDelete="CASCADE")
     */
    private $domaine;

    /**
     * @var array
     *
     * @ORM\Column(name="entglo_references", type="array", nullable=true)
     */
    private $references;


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
     * Set entityType
     *
     * @param integer $entityType
     *
     * @return EntityHasGlossaire
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * Get entityType
     *
     * @return integer
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Set entityId
     *
     * @param integer $entityId
     *
     * @return EntityHasGlossaire
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
     * Set references
     *
     * @param array $references
     *
     * @return EntityHasGlossaire
     */
    public function setReferences($references)
    {
        $this->references = $references;

        return $this;
    }

    /**
     * Get references
     *
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Set domaine
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     *
     * @return EntityHasGlossaire
     */
    public function setDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        $this->domaine = $domaine;

        return $this;
    }

    /**
     * Get domaine
     *
     * @return \HopitalNumerique\DomaineBundle\Entity\Domaine
     */
    public function getDomaine()
    {
        return $this->domaine;
    }
}
