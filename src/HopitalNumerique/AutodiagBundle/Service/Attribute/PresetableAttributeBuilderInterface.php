<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute;

use HopitalNumerique\AutodiagBundle\Entity\Model;
use Symfony\Component\Form\FormInterface;

/**
 * All attribute builders representing field who are presetable must implement this
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
interface PresetableAttributeBuilderInterface
{
    /**
     * Get preset options by Model
     *
     * @param Model $model
     * @return mixed
     */
    public function getPreset(Model $model);

    /**
     * Set preset options by model
     *
     * @param Model $model
     * @param $preset
     */
    public function setPreset(Model $model, $preset);

    /**
     * Get preset admin form
     *
     * @return FormInterface
     */
    public function getPresetForm();
}
