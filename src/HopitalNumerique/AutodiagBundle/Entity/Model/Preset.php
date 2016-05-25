<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Model;
use HopitalNumerique\AutodiagBundle\Entity\Model\Attribute\Option;

/**
 * Preset attribute options
 *
 * @ORM\Table(name="ad_model_preset")
 * @ORM\Entity
 */
class Preset
{
    /**
     * Model
     *
     * @var Model
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Model")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $model;

    /**
     * Attribute type
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * Preset value
     *
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $preset;

    /**
     * Preset constructor.
     * @param Model $model
     * @param $type
     */
    public function __construct(Model $model, $type)
    {
        $this->model = $model;
        $this->type = $type;

        $this->preset = [];
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
     * Get attribute type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get preset value
     *
     * @return array
     */
    public function getPreset()
    {
        return $this->preset;
    }

    /**
     * Set preset value
     *
     * @param array $preset
     * @return $this
     */
    public function setPreset($preset)
    {
        $this->preset = $preset;

        return $this;
    }
}
