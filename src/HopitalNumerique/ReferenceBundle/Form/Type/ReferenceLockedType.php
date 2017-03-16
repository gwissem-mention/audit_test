<?php

namespace HopitalNumerique\ReferenceBundle\Form\Type;

use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferenceLockedType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'required' => true,
                'label' => 'Libellé du concept',
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'validate[required]',
                    'data-prompt-position' => 'bottomLeft',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reference::class,
        ]);
    }

    public function getName()
    {
        return 'hopitalnumerique_reference_reference_locked';
    }
}
