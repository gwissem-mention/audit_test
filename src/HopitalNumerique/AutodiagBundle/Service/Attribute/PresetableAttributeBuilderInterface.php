<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
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
     * @param Autodiag $autodiag
     * @return mixed
     */
    public function getPreset(Autodiag $autodiag);

    /**
     * Set preset options by model
     *
     * @param Autodiag $autodiag
     * @param $preset
     */
    public function setPreset(Autodiag $autodiag, $preset);

    /**
     * Get preset admin form
     *
     * @return FormInterface
     */
    public function getPresetForm();

    /**
     * Get preset min score
     *
     * @param Autodiag $autodiag
     * @return mixed
     */
    public function getPresetMinScore(Autodiag $autodiag);

    /**
     * Get preset max score
     *
     * @param Autodiag $autodiag
     * @return mixed
     */
    public function getPresetMaxScore(Autodiag $autodiag);
}
