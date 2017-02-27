<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Attribute;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
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
        $resolver->setDefaults([
            'data_class' => null,
            'label_format' => 'ad.autodiag.%name%',
        ]);

        $resolver->setRequired([
            'attribute_builder',
            'autodiag',
            'attribute',
        ]);

        $resolver->setAllowedTypes('attribute_builder', [
            AttributeBuilderInterface::class,
        ]);

        $resolver->setAllowedTypes('autodiag', [
            Autodiag::class,
        ]);
    }
}
