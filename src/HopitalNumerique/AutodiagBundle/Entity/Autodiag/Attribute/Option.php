<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Option
 *
 * @ORM\Table(name="ad_autodiag_attribute_option")
 * @ORM\Entity
 */
class Option
{
    /**
     * @var Attribute
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute", inversedBy="options")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $attribute;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $label;

    public function __construct(Attribute $attribute, $value, $label = null)
    {
        $this->attribute = $attribute;
        $this->value = $value;
        $this->label = $label;
    }

    /**
     * @return Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
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
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}

