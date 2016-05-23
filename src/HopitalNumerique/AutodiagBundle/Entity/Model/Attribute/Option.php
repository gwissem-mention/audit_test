<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Model\Attribute;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Model\Attribute;

/**
 * Option
 *
 * @ORM\Table(name="ad_model_attribute_option")
 * @ORM\Entity
 */
class Option
{
    /**
     * @var Attribute
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Model\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    private $attribute;

    /**
     * @var string
     *
     * @ORM\Id @ORM\Column(type="string", length=255)
     */
    private $option;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    public function __construct(Attribute $attribute, $name, $value)
    {
        $this->attribute = $attribute;
        $this->option = $name;
        $this->value = $value;
    }

    /**
     * @return Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Get option
     *
     * @return string
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}

