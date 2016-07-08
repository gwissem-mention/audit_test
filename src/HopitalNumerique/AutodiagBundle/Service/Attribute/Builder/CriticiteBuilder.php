<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use HopitalNumerique\AutodiagBundle\Form\Type\Attribute\CriticiteType;
use HopitalNumerique\AutodiagBundle\Form\Type\CsvType;
use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\PresetValueType;

/**
 * Criticite attribute builder
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute\Builder
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class CriticiteBuilder extends AbstractPresetableBuilder
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'criticite';
    }

    /**
     * @inheritdoc
     */
    public function getPresetForm()
    {
        $formBuilder = $this->formFactory
            ->createNamedBuilder(
                'preset',
                PresetValueType::class
            );

        $formBuilder
            ->add('occurence', CsvType::class)
            ->add('impact', CsvType::class)
        ;

        return $formBuilder->getForm();
    }

    public function getFormType()
    {
        return CriticiteType::class;
    }

    public function transform($data)
    {
        return json_decode($data, true);
    }

    public function reverseTransform($data)
    {
        return json_encode($data);
    }
}
