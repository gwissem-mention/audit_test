<?php

namespace HopitalNumerique\AutodiagBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * ModelType
 *
 * @package HopitalNumerique\AutodiagBundle\Form\Type
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class AutodiagType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('instructions')
            ->add('partialResultsAuthorized')
            ->add('synthesisAuthorized')
            ->add('domaines')
            ->add('questionnaire')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HopitalNumerique\AutodiagBundle\Entity\Autodiag',
            'label_format' => 'ad.autodiag.%name%'
        ));
    }
}
