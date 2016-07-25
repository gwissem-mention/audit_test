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
            ->add('occurence', CsvType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
            ])
            ->add('impact', CsvType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
            ])
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

    public function computeScore($data)
    {
        $data = $this->transform($data);
        if (in_array(null, $data)) {
            return null;
        }
        return array_sum($data) / count($data);
    }

    public function isEmpty($data)
    {
        $data = $this->transform($data);
        $empty = false;
        foreach ($data as $value) {
            $empty = $empty || ($value === null);
        }
        return $empty;
    }
}
