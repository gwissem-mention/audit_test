<?php

namespace HopitalNumerique\ReferenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwitchReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentReference', IntegerType::class, ['attr' => ['class' => 'validate[required]']])
            ->add('targetReference', IntegerType::class, ['attr' => ['class' => 'validate[required]']])
            ->add('keepHistory', CheckboxType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\ReferenceBundle\Domain\Command\SwitchReferenceCommand'
        ));
    }
}
