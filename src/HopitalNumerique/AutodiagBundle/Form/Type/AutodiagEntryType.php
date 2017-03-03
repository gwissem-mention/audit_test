<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type;

use HopitalNumerique\AutodiagBundle\Form\Type\AutodiagEntry\ValueType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutodiagEntryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('values', CollectionType::class, [
                'entry_type' => ValueType::class,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry',
            'label_format' => 'ad.autodiag.%name%',
        ]);
    }
}
