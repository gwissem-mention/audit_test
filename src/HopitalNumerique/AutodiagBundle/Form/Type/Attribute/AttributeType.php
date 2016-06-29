<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Attribute;

use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AttributeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attributeBuilder = $options['attribute_builder'];
        $builder->addModelTransformer(
            new CallbackTransformer(
                function ($value) use ($attributeBuilder) {
                    return $attributeBuilder->transform($value);
                },
                function ($value) use ($attributeBuilder) {
                    return $attributeBuilder->reverseTransform($value);
                }
            )
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value',
            'label_format' => 'ad.autodiag.%name%',
        ));

        $resolver->setRequired('attribute_builder');

        $resolver->setAllowedTypes('attribute_builder', [
            AttributeBuilderInterface::class
        ]);
    }
}
