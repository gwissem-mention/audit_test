<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Attribute;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MaitriseType extends AttributeType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($options) {
                    $presetObject = $options['attribute_builder']->getPreset($options['autodiag']);
                    if ($presetObject) {
                        $preset = $presetObject->getPreset();

                        $event->getForm()
                            ->add('occurence', ChoiceType::class, [
                                'choices' => $preset['occurence'],
                                'empty_value' => '-',
                            ])
                            ->add('impact', ChoiceType::class, [
                                'choices' => $preset['impact'],
                                'empty_value' => '-',
                            ])
                            ->add('action', ChoiceType::class, [
                                'choices' => $preset['action'],
                                'empty_value' => '-',
                            ])
                        ;
                    }
                }
            );

    }
}
