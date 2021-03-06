<?php

namespace HopitalNumerique\AutodiagBundle\Service\Attribute\Builder;

use HopitalNumerique\AutodiagBundle\Form\Type\Attribute\MaitriseType;
use HopitalNumerique\AutodiagBundle\Form\Type\CsvType;
use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\PresetValueType;

/**
 * Maitrise attribute builder.
 *
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class MaitriseBuilder extends AbstractPresetableBuilder
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'maitrise';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return 'maitrise';
    }

    /**
     * {@inheritdoc}
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
            ->add('action', CsvType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
            ])
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

    public function computeScore($data)
    {
        $data = $this->transform($data);
        if (!is_array($data) || in_array(null, $data) || in_array(-1, $data)) {
            return null;
        }

        return array_sum($data) / count($data);
    }

    public function isEmpty($data)
    {
        $data = $this->transform($data);

        if (null === $data) {
            return false;
        }

        if (!is_array($data)) {
            return false;
        }
        $empty = false;
        foreach ($data as $value) {
            $empty = $empty || ($value === null);
        }

        return $empty;
    }
}
