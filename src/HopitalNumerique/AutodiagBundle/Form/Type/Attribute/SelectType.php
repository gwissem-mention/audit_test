<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Attribute;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectType extends AttributeType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $choiceLoader = function (Options $options) {
            return new SelectChoiceLoader($options['attribute']);
        };

        $resolver->setDefaults([
            'choice_loader' => $choiceLoader,
            'choices_as_values' => true,
            'expanded' => false,
            'multiple' => false,
            'empty_value' => '-',
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
