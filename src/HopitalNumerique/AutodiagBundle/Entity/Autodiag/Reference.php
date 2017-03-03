<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

/**
 * Autodiag reference.
 *
 * @ORM\Table(name="ad_autodiag_reference")
 * @ORM\Entity()
 */
class Reference
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Reference number - public identifier.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * Reference label.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $label;

    /**
     * Reference value.
     *
     * @var int
     *
     * @ORM\Column(type="string", length=50)
     */
    private $value;

    /**
     * History entry type.
     *
     * @var int
     *
     * @ORM\Column(type="string", length=50)
     */
    private $color;

    /**
     * Model.
     *
     * @var Autodiag
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag", inversedBy="references")
     * @ORM\JoinColumn(name="autodiag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $autodiag;

    /**
     * Reference constructor.
     *
     * @param Autodiag $autodiag
     */
    public function __construct($number, Autodiag $autodiag)
    {
        $this->number = $number;
        $this->autodiag = $autodiag;
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get number.
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Get autodiag.
     *
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param int $color
     *
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }
}
