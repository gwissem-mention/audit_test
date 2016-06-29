<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type\Attribute;

use Symfony\Component\Form\FormBuilderInterface;

class TextType extends AttributeType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', \Symfony\Component\Form\Extension\Core\Type\TextType::class)
        ;
    }
}
