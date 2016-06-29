<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ModelType
 *
 * @package HopitalNumerique\AutodiagBundle\Form\Type
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class SynthesisType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Synthesis',
            'label_format' => 'ad.ynthesis.%name%'
        ));
    }
}
