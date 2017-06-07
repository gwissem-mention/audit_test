<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute\Option;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Attribute.
 *
 * @ORM\Table(name="ad_autodiag_attribute")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\Autodiag\AttributeRepository")
 */
class Attribute
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
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $number;

    /**
     * Text before question.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * Additional description.
     *
     * @var string
     *
     * @ORM\Column(name="additional_description", type="text", nullable=true)
     */
    private $additionalDescription;

    /**
     * Attribute label.
     *
     * @var string
     * @ORM\Column(type="string", length=512)
     * @Assert\NotBlank()
     * @Assert\Length(max="512")
     */
    private $label;

    /**
     * Attribute unit.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max="255")
     */
    private $unit;

    /**
     * Attribute type.
     *
     * @var int
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\Length(max="50")
     */
    private $type;

    /**
     * Colored attribute.
     *
     * @var bool
     * @ORM\Column(type="boolean")
     * @Assert\NotNull()
     */
    private $colored;

    /**
     * Inverse colored attribute.
     *
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $colorationInversed = false;

    /**
     * Tooltip.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $tooltip;

    /**
     * Order.
     *
     * @var float
     * @ORM\Column(name="position", type="float", nullable=true)
     */
    private $order;

    /**
     * Question options.
     *
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute\Option",
     *     mappedBy="attribute",
     *     cascade={"persist", "remove", "detach"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @Assert\Valid()
     */
    private $options;

    const TEXT_TYPE = 'texte';

    /**
     * @var Autodiag
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag", inversedBy="attributes")
     * @ORM\JoinColumn(name="autodiag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $autodiag;

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return Attribute
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     *
     * @return Attribute
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Attribute
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalDescription()
    {
        return $this->additionalDescription;
    }

    /**
     * Set additional description.
     *
     * @param string $additionalDescription
     *
     * @return Attribute
     */
    public function setAdditionalDescription($additionalDescription)
    {
        $this->additionalDescription = $additionalDescription;

        return $this;
    }

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return Attribute
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function getExtendedLabel()
    {
        if (null == $this->number) {
            return $this->label;
        } else {
            return sprintf('%s. %s', $this->number, $this->label);
        }
    }

    /**
     * Get unit.
     *
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set unit.
     *
     * @param string $unit
     *
     * @return Attribute
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return Attribute
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get is colored.
     *
     * @return bool
     */
    public function isColored()
    {
        return $this->colored;
    }

    /**
     * Set is colored.
     *
     * @param bool $colored
     *
     * @return Attribute
     */
    public function setColored($colored)
    {
        $this->colored = (bool) $colored;

        return $this;
    }

    /**
     * @return bool
     */
    public function isColorationInversed()
    {
        return $this->colorationInversed;
    }

    /**
     * @param bool $colorationInversed
     *
     * @return Attribute
     */
    public function setColorationInversed($colorationInversed)
    {
        $this->colorationInversed = $colorationInversed;

        return $this;
    }

    /**
     * Get tooltip content.
     *
     * @return string
     */
    public function getTooltip()
    {
        return $this->tooltip;
    }

    /**
     * Set tooltip content.
     *
     * @param string $tooltip
     *
     * @return Attribute
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order.
     *
     * @param $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get options.
     *
     * @return Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Add option.
     *
     * @param Option $option
     *
     * @return Attribute
     */
    public function addOption(Option $option)
    {
        $this->options->add($option);

        return $this;
    }

    /**
     * Remove option.
     *
     * @param Option $option
     *
     * @return Attribute
     */
    public function removeOption(Option $option)
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * Get model.
     *
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * Set Model.
     *
     * @param Autodiag $autodiag
     *
     * @return Attribute
     */
    public function setAutodiag(Autodiag $autodiag)
    {
        $this->autodiag = $autodiag;

        return $this;
    }
}
