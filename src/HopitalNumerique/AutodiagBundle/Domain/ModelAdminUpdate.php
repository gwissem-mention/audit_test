<?php
namespace HopitalNumerique\AutodiagBundle\Domain;

use HopitalNumerique\AutodiagBundle\Entity\Model;

/**
 * Domain class that represent the admin form for create and update action on a Model
 *
 * @package HopitalNumerique\AutodiagBundle\Domain
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class ModelAdminUpdate
{
    /** @var Model */
    private $model;

    /** @var array */
    private $presets;

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return array
     */
    public function getPresets()
    {
        return $this->presets;
    }

    /**
     * @param Model\Preset $preset
     */
    public function addPreset(Model\Preset $preset)
    {
        $this->presets[] = $preset;
    }
}
