<?php
namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use HopitalNumerique\AutodiagBundle\Form\Type\Attribute\MaitriseType;
use HopitalNumerique\AutodiagBundle\Form\Type\CsvType;
use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\PresetValueType;

/**
 * Maitrise attribute builder
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Attribute\Builder
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class MaitriseBuilder extends AbstractPresetableBuilder
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'maitrise';
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
            ->add('action', CsvType::class)
        ;

        return $formBuilder->getForm();
    }

    public function getFormType()
    {
        return MaitriseType::class;
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
