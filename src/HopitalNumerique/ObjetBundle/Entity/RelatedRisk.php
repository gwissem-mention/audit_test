<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @deprecated Use object identity relation instead
 * @ORM\Entity
 * @ORM\Table(name="hn_objet_related_risk")
 */
class RelatedRisk
{
    /**
     * @var Objet
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Objet", inversedBy="relatedRisks")
     * @ORM\JoinColumn(referencedColumnName="obj_id")
     */
    protected $object;

    /**
     * @var Risk
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Risk", inversedBy="relatedRisks")
     */
    protected $risk;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $position;

    /**
     * RelatedRisk constructor.
     *
     * @param Objet $object
     * @param Risk $risk
     * @param int $position
     */
    public function __construct(Objet $object, Risk $risk, $position)
    {
        $this->object = $object;
        $this->risk = $risk;
        $this->position = $position;
    }

    /**
     * @return Objet
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param Objet $object
     *
     * @return RelatedRisk
     */
    public function setObject(Objet $object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return Risk
     */
    public function getRisk()
    {
        return $this->risk;
    }

    /**
     * @param Risk $risk
     *
     * @return RelatedRisk
     */
    public function setRisk(Risk $risk)
    {
        $this->risk = $risk;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return RelatedRisk
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
