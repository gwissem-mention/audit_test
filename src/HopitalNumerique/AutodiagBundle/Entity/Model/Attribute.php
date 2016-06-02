<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Model;
use HopitalNumerique\AutodiagBundle\Entity\Model\Attribute\Option;

/**
 * Attribute
 *
 * @ORM\Table(name="ad_model_attribute")
 * @ORM\Entity
 */
class Attribute
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * Text before question
     *
     * @var string
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * Attribute label
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * Attribute type
     *
     * @var int
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * Colored attribute
     *
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $colored;

    /**
     * Tooltip
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $tooltip;

    /**
     * Question options
     *
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Model\Attribute\Option",
     *     mappedBy="attribute",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    private $options;

    /**
     * @var Model
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Model")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $model;

    public function __construct()
    {
        $this->options = new ArrayCollection();
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
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
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

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get is colored
     *
     * @return boolean
     */
    public function isColored()
    {
        return $this->colored;
    }

    /**
     * Set is colored
     *
     * @param boolean $colored
     * @return $this
     */
    public function setColored($colored)
    {
        $this->colored = (bool) $colored;

        return $this;
    }

    /**
     * Get tooltip content
     *
     * @return string
     */
    public function getTooltip()
    {
        return $this->tooltip;
    }

    /**
     * Set tooltip content
     *
     * @param string $tooltip
     * @return $this
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    /**
     * Get options
     *
     * @return Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Add option
     *
     * @param Option $option
     * @return $this
     */
    public function addOption(Option $option)
    {
        $this->options->add($option);

        return $this;
    }

    /**
     * Remove option
     *
     * @param Option $option
     * @return $this
     */
    public function removeOption(Option $option)
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * Get model
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set Model
     *
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }
}

