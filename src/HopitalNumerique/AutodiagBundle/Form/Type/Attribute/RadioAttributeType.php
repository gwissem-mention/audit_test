<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Attribute;

use Symfony\Component\OptionsResolver\OptionsResolver;

class RadioAttributeType extends AttributeType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'expanded' => true,
        ]);
    }

    public function getParent()
    {
        return SelectType::class;
    }
}
