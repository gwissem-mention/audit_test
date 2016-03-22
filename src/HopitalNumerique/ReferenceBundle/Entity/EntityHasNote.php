<?php
namespace HopitalNumerique\ReferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

/**
 * EntityHasReference.
 *
 * @ORM\Table(name="hn_entity_has_note", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ENTITY_DOMAINE", columns={"entnot_entity_id", "entnot_entity_type", "dom_id"})
 * })
 * @ORM\Entity()
 */
class EntityHasNote
{
    /**
     * @var integer Score maximal
     */
    const SCORE_GLOBAL = 1000;


    /**
     * @var integer
     *
     * @ORM\Column(name="entnot_id", type="integer", options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entnot_entity_id", type="integer", options={"unsigned"=true})
     */
    private $entityId;

    /**
     * @var string
     *
     * @ORM\Column(name="entnot_entity_type", type="string", length=16, nullable=false)
     */
    private $entityType;

    /**
     * @var \HopitalNumerique\DomaineBundle\Entity\Domaine
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")
     */
    private $domaine;

    /**
     * @var float
     *
     * @ORM\Column(name="entnot_note", type="decimal", nullable=false, precision=6, scale=2)
     */
    private $note;


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
     * @return EntityHasNote
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
     * @return EntityHasNote
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
     * Set domaine
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Domaine $domaine
     *
     * @return EntityHasNote
     */
    public function setDomaine(Domaine $domaine = null)
    {
        $this->domaine = $domaine;

        return $this;
    }

    /**
     * Get domaine
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Domaine
     */
    public function getDomaine()
    {
        return $this->domaine;
    }

    /**
     * Set note
     *
     * @param float $note
     *
     * @return EntityHasNote
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return float
     */
    public function getNote()
    {
        return $this->note;
    }
}
